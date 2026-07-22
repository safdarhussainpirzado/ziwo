<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

/**
 * Live integration tests for the softphone endpoints.
 *
 * Strategy: stub the ZIWO proxy with Http::fake() so the test never actually
 * hits nayatel-api.aswat.co. This exercises the controller + service layer
 * (request validation, session cache, DB persist, proxy call, response shape)
 * and gives us a single place to assert that the live ZIWO contract still
 * matches what our app sends.
 */
class SoftphoneAuthTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        // Softphone endpoints are stateless API calls behind Bearer/session auth;
        // CSRF token is sent as X-CSRF-TOKEN header in production JS, but the
        // Laravel test client doesn't set it automatically. Strip the middleware.
        $this->withoutMiddleware([ValidateCsrfToken::class]);
        $this->user = User::first() ?? User::factory()->create();
    }

    public function test_authenticate_returns_error_for_missing_credentials(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/telephony/authenticate', []);

        $response->assertStatus(422);
        $response->assertJsonStructure(['status', 'errors' => ['username', 'password']]);
    }

    public function test_authenticate_proxies_to_ziwo_and_persists_token(): void
    {
        Http::fake([
            '*/auth/login' => Http::response([
                'result'  => true,
                'content' => [
                    'access_token' => 'live_test_token_xyz',
                    'username'     => $this->user->email,
                    'ccLogin'      => '1001',
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->postJson('/telephony/authenticate', [
            'username' => $this->user->email,
            'password' => 'live-test-pw',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'          => 'success',
            'ziwo_username'   => $this->user->email,
            'agent_status'    => 'online',
            'contact_center'  => 'nayatel',
        ]);
        $response->assertJsonStructure(['access_token', 'contact_center']);

        // Token should be persisted on the agent config row
        $row = DB::table('telephony_agent_configs')->where('user_id', $this->user->id)->first();
        $this->assertNotNull($row, 'agent config row should exist after successful auth');
        $this->assertEquals('live_test_token_xyz', $row->ziwo_token);
    }

    public function test_authenticate_propagates_ziwo_4xx_as_401(): void
    {
        Http::fake([
            '*/auth/login' => Http::response([
                'result'  => false,
                'message' => 'Invalid credentials',
            ], 401),
        ]);

        $response = $this->actingAs($this->user)->postJson('/telephony/authenticate', [
            'username' => $this->user->email,
            'password' => 'wrong',
        ]);

        $response->assertStatus(401);
        $response->assertJsonFragment(['message' => 'Invalid credentials']);
    }

    public function test_get_status_returns_offline_when_no_agent_config(): void
    {
        // Wipe any prior config for this user
        DB::table('telephony_agent_configs')->where('user_id', $this->user->id)->delete();

        $response = $this->actingAs($this->user)
            ->withHeaders(['Accept' => 'application/json'])
            ->getJson('/telephony/status');

        $response->assertStatus(200);
        $response->assertJson([
            'agent_status'     => 'offline',
            'is_authenticated' => false,
        ]);
    }

    public function test_set_status_validates_against_allow_list(): void
    {
        $response = $this->actingAs($this->user)->postJson('/telephony/status/set', [
            'status' => 'invalid_value',
        ]);
        $response->assertStatus(422);
    }

    public function test_set_status_rejects_offline_value(): void
    {
        // Per product decision: agents can't set themselves "offline" via this
        // endpoint — that's what the disconnect flow is for.
        $response = $this->actingAs($this->user)->postJson('/telephony/status/set', [
            'status' => 'offline',
        ]);
        $response->assertStatus(422);
    }

    public function test_set_status_requires_authenticated_agent_config(): void
    {
        DB::table('telephony_agent_configs')->where('user_id', $this->user->id)->delete();

        $response = $this->actingAs($this->user)->postJson('/telephony/status/set', [
            'status' => 'available',
        ]);
        $response->assertStatus(400);
        $response->assertJsonFragment(['message' => 'Agent not authenticated with telephony gateway']);
    }

    public function test_set_status_proxies_to_ziwo_and_returns_agent_status(): void
    {
        // Seed an agent config row so the controller can read the token
        DB::table('telephony_agent_configs')->updateOrInsert(
            ['user_id' => $this->user->id],
            [
                'ziwo_username' => $this->user->email,
                'ziwo_token'    => 'test_tok_abc',
                'agent_status'  => 'online',
                'expires_at'    => now()->addHour(),
                'updated_at'    => now(),
                'created_at'    => now(),
            ]
        );

        Http::fake([
            '*/agent/statuses*' => Http::response([
                'result'  => true,
                'content' => [
                    ['id' => 1, 'name' => 'Available', 'behaviour' => 'Available'],
                    ['id' => 2, 'name' => 'On Break',  'behaviour' => 'On Break'],
                    ['id' => 3, 'name' => 'Meeting',   'behaviour' => 'Meeting'],
                    ['id' => 4, 'name' => 'Outgoing',  'behaviour' => 'Outgoing'],
                ],
            ], 200),
            '*/agents/status' => Http::response([
                'status'       => 'success',
                'agent_status' => 'meeting',
                'username'     => $this->user->email,
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->postJson('/telephony/status/set', [
            'status' => 'meeting',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status'       => 'success',
            'agent_status' => 'meeting',
        ]);

        // Local DB row was updated
        $row = DB::table('telephony_agent_configs')->where('user_id', $this->user->id)->first();
        $this->assertEquals('meeting', $row->agent_status);
    }

    public function test_set_status_surfaces_proxy_error(): void
    {
        DB::table('telephony_agent_configs')->updateOrInsert(
            ['user_id' => $this->user->id],
            [
                'ziwo_username' => $this->user->email,
                'ziwo_token'    => 'test_tok_abc',
                'agent_status'  => 'online',
                'expires_at'    => now()->addHour(),
                'updated_at'    => now(),
                'created_at'    => now(),
            ]
        );

        Http::fake([
            '*/agent/statuses*' => Http::response([
                'result'  => true,
                'content' => [
                    ['id' => 1, 'name' => 'Available', 'behaviour' => 'Available'],
                ],
            ], 200),
            '*/agents/status' => Http::response(['message' => 'Forbidden'], 403),
        ]);

        $response = $this->actingAs($this->user)->postJson('/telephony/status/set', [
            'status' => 'available',
        ]);

        $response->assertStatus(200);
        // Service translates proxy 4xx into { status: 'error', message: ... }
        $response->assertJson(['status' => 'error']);
    }

    public function test_set_status_uses_dynamic_status_id_from_ziwo(): void
    {
        // If admin renumbers statuses in the ZIWO console, we should pick
        // up the new IDs from /agent/statuses, not hardcode 1-4.
        DB::table('telephony_agent_configs')->updateOrInsert(
            ['user_id' => $this->user->id],
            [
                'ziwo_username' => $this->user->email,
                'ziwo_token'    => 'test_tok_abc',
                'agent_status'  => 'online',
                'expires_at'    => now()->addHour(),
                'updated_at'    => now(),
                'created_at'    => now(),
            ]
        );

        Http::fake([
            '*/agent/statuses*' => Http::response([
                'result'  => true,
                'content' => [
                    ['id' => 17, 'name' => 'Available', 'behaviour' => 'Available'],
                    ['id' => 24, 'name' => 'On Break',  'behaviour' => 'On Break'],
                    ['id' => 9,  'name' => 'Meeting',   'behaviour' => 'Meeting'],
                    ['id' => 3,  'name' => 'Outgoing',  'behaviour' => 'Outgoing'],
                ],
            ], 200),
            '*/agents/status' => Http::response(['result' => true], 200),
        ]);

        $this->actingAs($this->user)->postJson('/telephony/status/set', [
            'status' => 'available',
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'agents/status')
                && $request['number'] === 17;
        });
    }

    public function test_queues_endpoint_returns_ziwo_queues(): void
    {
        DB::table('telephony_agent_configs')->updateOrInsert(
            ['user_id' => $this->user->id],
            [
                'ziwo_username' => $this->user->email,
                'ziwo_token'    => 'test_tok_abc',
                'agent_status'  => 'online',
                'expires_at'    => now()->addHour(),
                'updated_at'    => now(),
                'created_at'    => now(),
            ]
        );

        Http::fake([
            '*/agent/queues*' => Http::response([
                'result'  => true,
                'content' => [
                    ['id' => 'q1', 'name' => 'Support', 'extension' => '3001', 'agents' => []],
                    ['id' => 'q2', 'name' => 'Sales',   'extension' => '3002', 'agents' => []],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->getJson('/telephony/queues');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'queues');
        $response->assertJsonFragment(['name' => 'Support', 'number' => '3001']);
    }

    public function test_queues_endpoint_returns_error_when_proxy_fails(): void
    {
        DB::table('telephony_agent_configs')->updateOrInsert(
            ['user_id' => $this->user->id],
            [
                'ziwo_username' => $this->user->email,
                'ziwo_token'    => 'test_tok_abc',
                'agent_status'  => 'online',
                'expires_at'    => now()->addHour(),
                'updated_at'    => now(),
                'created_at'    => now(),
            ]
        );

        Http::fake([
            '*/agent/queues*' => Http::response('Gateway Unreachable', 502),
        ]);

        $response = $this->actingAs($this->user)->getJson('/telephony/queues');

        // No mock data fallback — surface real error
        $response->assertStatus(502);
        $response->assertJson(['status' => 'error', 'queues' => []]);
    }

    public function test_teammates_endpoint_returns_ziwo_admins(): void
    {
        DB::table('telephony_agent_configs')->updateOrInsert(
            ['user_id' => $this->user->id],
            [
                'ziwo_username' => $this->user->email,
                'ziwo_token'    => 'test_tok_abc',
                'agent_status'  => 'online',
                'expires_at'    => now()->addHour(),
                'updated_at'    => now(),
                'created_at'    => now(),
            ]
        );

        Http::fake([
            '*/agents/channels/calls/listAgents' => Http::response([
                'result'  => true,
                'content' => [
                    ['id' => 1, 'firstName' => 'Alice',  'lastName' => 'A.',    'ccLogin' => '101', 'status' => 'active'],
                    ['id' => 2, 'firstName' => 'Bob',    'lastName' => 'B.',    'ccLogin' => '102', 'status' => 'inactive'],
                    ['id' => 3, 'firstName' => 'Carol',  'lastName' => 'C.',    'ccLogin' => '103', 'status' => 'active'],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->getJson('/telephony/teammates');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'teammates');
        $response->assertJsonFragment(['name' => 'Alice A.', 'ext' => '101', 'status' => 'online']);
        $response->assertJsonFragment(['name' => 'Bob B.',   'ext' => '102', 'status' => 'offline']);
    }

    public function test_status_endpoint_redirects_plain_browser_navigation(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/telephony/status');  // no Accept: application/json header

        // Should redirect to the user's landing page, NOT return raw JSON
        $this->assertContains($response->getStatusCode(), [302, 301]);
    }

    public function test_crm_search_returns_ziwo_customers(): void
    {
        DB::table('telephony_agent_configs')->updateOrInsert(
            ['user_id' => $this->user->id],
            [
                'ziwo_username' => $this->user->email,
                'ziwo_token'    => 'test_tok_abc',
                'agent_status'  => 'online',
                'expires_at'    => now()->addHour(),
                'updated_at'    => now(),
                'created_at'    => now(),
            ]
        );

        Http::fake([
            '*/agent/crm/customers*' => Http::response([
                'result'  => true,
                'content' => [
                    ['id' => 'c1', 'firstName' => 'Safdar',  'lastName' => 'Hussain', 'phone' => '+923423695277', 'tags' => []],
                    ['id' => 'c2', 'firstName' => null,      'lastName' => null,      'phone' => '+923001234567', 'tags' => []],
                    ['id' => 'c3', 'firstName' => 'Ahmed',   'lastName' => 'Khan',    'phone' => '03215026677',   'tags' => []],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->getJson('/telephony/crm/search?query=+9234');

        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'contacts']);
        $names = collect($response->json('contacts'))->pluck('name');
        $this->assertContains('Safdar Hussain', $names->all());
    }

    public function test_crm_search_server_side_filters_by_query(): void
    {
        DB::table('telephony_agent_configs')->updateOrInsert(
            ['user_id' => $this->user->id],
            [
                'ziwo_username' => $this->user->email,
                'ziwo_token'    => 'test_tok_abc',
                'agent_status'  => 'online',
                'expires_at'    => now()->addHour(),
                'updated_at'    => now(),
                'created_at'    => now(),
            ]
        );

        Http::fake([
            '*/agent/crm/customers*' => Http::response([
                'result'  => true,
                'content' => [
                    ['id' => 'c1', 'firstName' => 'Alice',  'lastName' => 'A.', 'phone' => '+923000000001', 'tags' => []],
                    ['id' => 'c2', 'firstName' => 'Bob',    'lastName' => 'B.', 'phone' => '+923000000002', 'tags' => []],
                ],
            ], 200),
        ]);

        // query=alice → only Alice
        $r = $this->actingAs($this->user)->getJson('/telephony/crm/search?query=alice');
        $r->assertStatus(200);
        $this->assertCount(1, $r->json('contacts'));
        $this->assertEquals('Alice A.', $r->json('contacts.0.name'));

        // query=+923000000002 → only Bob
        $r2 = $this->actingAs($this->user)->getJson('/telephony/crm/search?query=%2B923000000002');
        $r2->assertStatus(200);
        $this->assertCount(1, $r2->json('contacts'));
        $this->assertEquals('Bob B.', $r2->json('contacts.0.name'));
    }

    public function test_crm_search_returns_error_on_proxy_failure(): void
    {
        DB::table('telephony_agent_configs')->updateOrInsert(
            ['user_id' => $this->user->id],
            [
                'ziwo_username' => $this->user->email,
                'ziwo_token'    => 'test_tok_abc',
                'agent_status'  => 'online',
                'expires_at'    => now()->addHour(),
                'updated_at'    => now(),
                'created_at'    => now(),
            ]
        );

        Http::fake([
            '*/agent/crm/customers*' => Http::response('Bad Gateway', 502),
        ]);

        $response = $this->actingAs($this->user)->getJson('/telephony/crm/search');
        $response->assertStatus(502);
        $response->assertJson(['status' => 'error', 'contacts' => []]);
    }

    public function test_live_call_history_returns_ziwo_calls(): void
    {
        DB::table('telephony_agent_configs')->updateOrInsert(
            ['user_id' => $this->user->id],
            [
                'ziwo_username' => $this->user->email,
                'ziwo_token'    => 'test_tok_abc',
                'agent_status'  => 'online',
                'expires_at'    => now()->addHour(),
                'updated_at'    => now(),
                'created_at'    => now(),
            ]
        );

        Http::fake([
            '*/agents/channels/calls*' => Http::response([
                'result'  => true,
                'content' => [
                    [
                        'callID'          => 'live-call-001',
                        'callerIDNumber'  => '03221714682',
                        'direction'       => 'inbound',
                        'disposition'    => 'ANSWER',
                        'duration'       => 90,
                        'result'         => 'answered',
                        'startedAt'      => '2026-07-20T22:55:57.000Z',
                        'endedAt'        => '2026-07-20T22:58:20.000Z',
                        'queueName'      => 'MAIN',
                        'recordingFile'  => 'live-call-001.mp3',
                        'customer'       => ['firstName' => 'Safdar', 'lastName' => 'Pirzado'],
                    ],
                    [
                        'callID'          => 'live-call-002',
                        'callerIDNumber'  => '8778150',
                        'didCalled'       => '+923002551224',
                        'channelName'     => 'verto.rtc/+923002551224',
                        'direction'       => 'outbound',
                        'disposition'    => 'EARLY MEDIA',
                        'duration'       => 1,
                        'result'         => 'no-answer',
                        'startedAt'      => '2026-07-20T22:57:30.000Z',
                        'endedAt'        => '2026-07-20T22:57:31.000Z',
                        'recordingFile'  => null,
                    ],
                ],
                'info' => [],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->getJson('/telephony/calls/live?limit=15');

        $response->assertStatus(200);
        $response->assertJsonStructure(['status', 'count', 'calls']);
        $this->assertCount(2, $response->json('calls'));
        $this->assertEquals('live-call-001', $response->json('calls.0.call_id'));
        $this->assertEquals('03221714682',    $response->json('calls.0.caller_number'));
        $this->assertEquals('Safdar Pirzado', $response->json('calls.0.caller_name'));
        $this->assertEquals('inbound',        $response->json('calls.0.direction'));
        $this->assertEquals('answered',       $response->json('calls.0.status'));
        $this->assertEquals('MAIN',           $response->json('calls.0.queue_name'));
        $this->assertEquals('live-call-001.mp3', $response->json('calls.0.recording_file'));
        $this->assertEquals('ziwo_live',      $response->json('calls.0.source'));
        $this->assertEquals('outbound',       $response->json('calls.1.direction'));
        $this->assertNull($response->json('calls.1.recording_file'));
        // Outbound call: caller_number must show the DIALLED destination,
        // not the PBX trunk (8778150).
        $this->assertEquals('+923002551224',  $response->json('calls.1.caller_number'));
        $this->assertEquals('+923002551224',  $response->json('calls.1.dialed_number'));
        $this->assertEquals('+923002551224',  $response->json('calls.1.redial_number'));
        $this->assertEquals('no-answer',      $response->json('calls.1.status'));
        $this->assertNotNull($response->json('calls.0.time_ago'));
        $this->assertNotNull($response->json('calls.0.started_at_iso'));
    }

    public function test_live_call_history_requires_agent_config(): void
    {
        DB::table('telephony_agent_configs')->where('user_id', $this->user->id)->delete();

        $response = $this->actingAs($this->user)->getJson('/telephony/calls/live');
        $response->assertStatus(400);
        $response->assertJson(['status' => 'error', 'calls' => []]);
    }

    public function test_live_call_history_surfaces_proxy_error(): void
    {
        DB::table('telephony_agent_configs')->updateOrInsert(
            ['user_id' => $this->user->id],
            [
                'ziwo_username' => $this->user->email,
                'ziwo_token'    => 'test_tok_abc',
                'agent_status'  => 'online',
                'expires_at'    => now()->addHour(),
                'updated_at'    => now(),
                'created_at'    => now(),
            ]
        );

        Http::fake([
            '*/agents/channels/calls*' => Http::response([
                'result'  => false,
                'message' => 'upstream gone',
            ], 502),
        ]);

        $response = $this->actingAs($this->user)->getJson('/telephony/calls/live');
        $response->assertStatus(502);
        $response->assertJson(['status' => 'error', 'calls' => []]);
    }
}
