<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Get valid relational IDs
        $agentId = DB::table('users')->where('username', 'nhmp_admin')->value('id');
        if (!$agentId) {
            $agentId = DB::table('users')->first()->id ?? 1;
        }

        $callTypes = DB::table('call_types')->pluck('id')->toArray();
        $vehicleTypes = DB::table('vehicle_types')->pluck('id')->toArray();
        $carriageways = DB::table('carriageways')->pluck('id')->toArray();
        $zones = DB::table('zones')->pluck('id')->toArray();
        $sectors = DB::table('sectors')->pluck('id')->toArray();
        $beats = DB::table('beats')->pluck('id')->toArray();

        if (empty($callTypes)) {
            return;
        }

        $now = Carbon::now();
        $statuses = ['pending', 'in_progress', 'completed', 'cancelled', 'junk'];
        $priorities = [1, 2, 3, 4, 5];

        for ($i = 0; $i < 100; $i++) {
            $callTypeId = $callTypes[array_rand($callTypes)];
            $subTypeId = DB::table('call_sub_types')->where('call_type_id', $callTypeId)->pluck('id')->shuffle()->first();
            
            $startTime = $now->copy()->subHours(rand(1, 168))->subMinutes(rand(0, 59));
            $endTime = $startTime->copy()->addMinutes(rand(5, 45));

            DB::table('calls')->insert([
                'call_number' => 'CAL-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
                'agent_id' => $agentId,
                'caller_number' => '03' . rand(0, 4) . rand(1000000, 9999999),
                'caller_name' => 'Citizen User ' . ($i + 1),
                'call_type_id' => $callTypeId,
                'call_sub_type_id' => $subTypeId,
                'details' => 'Synthetic data for NHMP 130 Helpline UI verification and dashboard load testing.',
                'vehicle_type_id' => !empty($vehicleTypes) ? $vehicleTypes[array_rand($vehicleTypes)] : null,
                'vehicle_no' => strtoupper(Str::random(3)) . '-' . rand(100, 999),
                'carriageway_id' => !empty($carriageways) ? $carriageways[array_rand($carriageways)] : null,
                'zone_id' => !empty($zones) ? $zones[array_rand($zones)] : null,
                'sector_id' => !empty($sectors) ? $sectors[array_rand($sectors)] : null,
                'beat_id' => !empty($beats) ? $beats[array_rand($beats)] : null,
                'status' => $statuses[array_rand($statuses)],
                'priority' => $priorities[array_rand($priorities)],
                'call_start_time' => $startTime,
                'call_pickup_time' => $startTime->copy()->addSeconds(rand(5, 30)),
                'call_end_time' => $endTime,
                'wait_time_seconds' => rand(5, 30),
                'agent_call_duration' => abs($startTime->diffInSeconds($endTime)),
                'created_at' => $startTime,
                'updated_at' => $endTime,
            ]);
        }
    }
}
