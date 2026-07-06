<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Office;
use App\Models\Designation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NHMPSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->command->info('Starting NHMP Production-Ready Data Sync...');

        // 1. Geography (IG HQ -> Regions -> Zones -> Sectors -> Beats)
        $this->call(OfficeSeeder::class);

        // 2. Designations
        $this->call(DesignationSeeder::class);

        // 4. Operational Assets
        $this->call(CarriagewaySeeder::class);
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('NHMP Production-Ready structure seeded successfully.');
    }
}
