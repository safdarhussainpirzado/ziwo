<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Designation;
use Illuminate\Support\Facades\File;

class DesignationSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('seeders/data/designations.json');
        
        if (File::exists($jsonPath)) {
            $designations = json_decode(File::get($jsonPath), true);
            foreach ($designations as $d) {
                Designation::updateOrCreate(
                    ['short_code' => $d['short_code']],
                    [
                        'name' => $d['name'],
                        'bps' => $d['bps'],
                        'similar_rank' => $d['similar_rank'],
                        'type' => $d['type'],
                        'is_active' => (bool)$d['is_active'],
                    ]
                );
            }
            $this->command->info('Designations seeded from JSON data.');
        } else {
            // Fallback for minimal bootstrap
            $minimal = [
                ['name' => 'Inspector General', 'short_code' => 'IG', 'bps' => 22, 'type' => 'Uniform', 'similar_rank' => 'IG', 'is_active' => 1],
                ['name' => 'Addl. Inspector General', 'short_code' => 'Addl.IG', 'bps' => 21, 'type' => 'Uniform', 'similar_rank' => 'Addl.IG', 'is_active' => 1],
                ['name' => 'Deputy Inspector General', 'short_code' => 'DIG', 'bps' => 20, 'type' => 'Uniform', 'similar_rank' => 'DIG', 'is_active' => 1],
                ['name' => 'Sector Commander', 'short_code' => 'SC', 'bps' => 19, 'type' => 'Uniform', 'similar_rank' => 'SSP', 'is_active' => 1],
            ];
            foreach ($minimal as $d) {
                Designation::updateOrCreate(['short_code' => $d['short_code']], $d);
            }
            $this->command->warn('Designation JSON not found. Seeded minimal bootstrap data.');
        }
    }
}
