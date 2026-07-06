<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Call;
use App\Models\CallType;
use App\Models\CallSubType;
use App\Models\Beat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use App\Events\CallLogged;

class CallReminderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    public function test_can_save_call_with_location_details()
    {
        $user = User::first();
        $callType = CallType::first();
        $subType = CallSubType::where('call_type_id', $callType->id)->first();
        $beat = Beat::first();

        $response = $this->actingAs($user)->post('/calls', [
            'caller_number' => '03001234567',
            'caller_name' => 'Test Caller',
            'call_type_id' => $callType->id,
            'call_sub_type_id' => $subType->id,
            'beat_id' => $beat->id,
            'location_details' => 'Near Rehman Masjid',
            'priority' => 3,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('calls', [
            'location_details' => 'Near Rehman Masjid',
            'caller_number' => '03001234567'
        ]);
    }

    public function test_can_send_reminder_via_api()
    {
        Event::fake();

        $user = User::first();
        $callType = CallType::first();
        $subType = CallSubType::where('call_type_id', $callType->id)->first();
        $beat = Beat::first();

        $call = Call::create([
            'call_number' => 'CRM-2026-99999',
            'agent_id' => $user->id,
            'caller_number' => '03001234567',
            'call_type_id' => $callType->id,
            'call_sub_type_id' => $subType->id,
            'beat_id' => $beat->id,
            'status' => 'pending',
            'priority' => 3,
        ]);

        $response = $this->actingAs($user)->postJson("/api/calls/{$call->id}/reminder");

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('call.call_reminder_count', 1);

        $this->assertDatabaseHas('calls', [
            'id' => $call->id,
            'call_reminder_count' => 1,
        ]);

        $this->assertNotNull($call->fresh()->last_reminder_at);

        Event::assertDispatched(CallLogged::class);
    }
}
