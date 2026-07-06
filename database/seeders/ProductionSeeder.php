<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ProductionSeeder extends Seeder
{
    /**
     * Run the production-ready structural database seeds.
     * Includes:
     * - NHMP Hierarchy (Regions, Zones, Sectors, Beats, Designations)
     * - Roles & Permissions
     * - Call Types, Sub-Types & Vehicles
     * - System Settings
     * - Single Super Admin (nhmp_admin)
     */
    public function run(): void
    {
        $this->command->info('Starting Production Seeder (Structural Only)...');
        $now = Carbon::now();

        // 1. NHMP Structure (Regions, Zones, Sectors, Beats, Designations, Carriageways)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->command->info('Starting NHMP Production-Ready Data Sync...');

        // 1. Geography (IG HQ -> Regions -> Zones -> Sectors -> Beats)
        $this->call(OfficeSeeder::class);

        // 2. Designations
        $this->call(DesignationSeeder::class);

        // 4. Operational Assets
        $this->call(CarriagewaySeeder::class);
        $this->call(GeospatialLandmarkSeeder::class);
        $this->call(GeospatialMarkerSeeder::class);
  
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 2. Roles & Permissions setup
        $this->call(RolesAndPermissionsSeeder::class);

        // 4. Call Types, Sub-Types & Vehicle Types (Categorized & Styled)
        $this->call(NHMPCategorySeeder::class);

        // 6. System Settings
        $this->seedSystemSettings();

        // 7. Default Super Admin (Machine B / Fresh Setup)
        $this->command->info('Creating default Super Admin (nhmp_admin)...');
        DB::table('users')->updateOrInsert(
            ['username' => '130_crm_admin'],
            [
                'full_name' => 'NHMP Super Admin',
                'password' => Hash::make('Secret@123Admin!'),
                'email' => '130admin@nhmp.gov.pk',
                'mobile_no' => '00000000000',
                'cnic' => '00000-0000000-0',
                'designation_id' => 1,
                'role_id' => 1, // super_admin
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $this->command->info('✔ Super Admin (nhmp_admin) created successfully!');
    }

    private function seedSystemSettings(): void
    {
        $settings = [
            ['key_name' => 'app_name',              'value' => 'NHMP 130 Helpline CRM'],
            ['key_name' => 'dashboard_refresh_sec', 'value' => '30'],
            ['key_name' => 'alert_refresh_sec',     'value' => '15'],
            ['key_name' => 'session_timeout_agent', 'value' => '15'],
            ['key_name' => 'session_timeout_admin', 'value' => '15'],
            ['key_name' => 'sla_p1_minutes',        'value' => '10'],
            ['key_name' => 'call_edit_window_min',  'value' => '10'],
        ];

        foreach ($settings as $setting) {
            DB::table('system_settings')->updateOrInsert(
                ['key_name' => $setting['key_name']],
                ['value' => $setting['value']]
            );
        }
    }
}
