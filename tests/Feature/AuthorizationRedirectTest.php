<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class AuthorizationRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    /** @test */
    public function agent_is_redirected_from_dashboard_to_calls_create()
    {
        $agent = User::whereHas('role', fn($q) => $q->where('name', 'agent'))->first();
        
        $response = $this->actingAs($agent)->get('/dashboard');
        
        $response->assertRedirect(route('calls.create'));
        $response->assertSessionHas('error', 'Unauthorized access. You have been redirected to your authorized workspace.');
    }

    /** @test */
    public function supervisor_is_redirected_from_calls_create_to_calls_index()
    {
        $supervisor = User::whereHas('role', fn($q) => $q->where('name', 'agent_supervisor'))->first();
        
        $response = $this->actingAs($supervisor)->get(route('calls.create'));
        
        $response->assertRedirect(route('calls.index'));
        $response->assertSessionHas('error', 'Unauthorized access. You have been redirected to your authorized workspace.');
    }

    /** @test */
    public function supervisor_cannot_submit_call_creation_form()
    {
        $supervisor = User::whereHas('role', fn($q) => $q->where('name', 'agent_supervisor'))->first();
        $callType = DB::table('call_types')->first();
        
        $response = $this->actingAs($supervisor)->post('/calls', [
            'caller_number' => '03001234567',
            'call_type_id' => $callType->id,
            'caller_name' => 'John Doe',
        ]);
        
        $response->assertRedirect(route('calls.index'));
        $response->assertSessionHas('error', 'Unauthorized access. You have been redirected to your authorized workspace.');
        
        $this->assertDatabaseMissing('calls', [
            'caller_number' => '03001234567',
        ]);
    }
}
