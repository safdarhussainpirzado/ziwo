<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Office;
use Illuminate\Support\Facades\File;

class OfficeSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Root HQ (PLHQ)
        $hq = Office::updateOrCreate(
            ['name' => 'IG Headquarter', 'type' => 'plhq'],
            [
                'operational_type' => 'office',
                'is_active' => true,
                'parent_id' => null,
            ]
        );

        // 2. Create Default Call Center
        Office::updateOrCreate(
            ['name' => 'Main Call Center', 'type' => 'call_center'],
            [
                'operational_type' => 'office',
                'is_active' => true,
                'parent_id' => $hq->id,
            ]
        );

        $jsonPath = database_path('seeders/data/offices_data.json');
        
        if (File::exists($jsonPath)) {
            $data = json_decode(File::get($jsonPath), true);
            
            // Sync Regions
            foreach ($data['regions'] as $r) {
                $region = Office::updateOrCreate(
                    ['name' => $r['name'], 'type' => 'region'],
                    [
                        'operational_type' => $r['operational_type'] ?? 'field',
                        'is_active' => true,
                        'parent_id' => $hq->id,
                    ]
                );
                $regionMap[$r['id']] = $region->id;
            }

            // Sync Zones
            foreach ($data['zones'] as $z) {
                $parent_id = $regionMap[$z['region_id']] ?? null;
                $zone = Office::updateOrCreate(
                    ['name' => $z['name'], 'type' => 'zone', 'parent_id' => $parent_id],
                    [
                        'operational_type' => $z['operational_type'] ?? 'field',
                        'phone' => $z['phone'] ?? null,
                        'is_active' => true,
                    ]
                );
                $zoneMap[$z['id']] = $zone->id;
            }

            // Sync Sectors
            foreach ($data['sectors'] as $s) {
                $parent_id = $zoneMap[$s['zone_id']] ?? null;
                $sector = Office::updateOrCreate(
                    ['name' => $s['name'], 'type' => 'sector', 'parent_id' => $parent_id],
                    [
                        'phone' => $s['phone'] ?? null,
                        'short_name' => $s['short_name'] ?? null,
                        'is_active' => true,
                    ]
                );
                $sectorMap[$s['id']] = $sector->id;
            }

            // Sync Beats
            foreach ($data['beats'] as $b) {
                $parent_id = $sectorMap[$b['sector_id']] ?? null;
                Office::updateOrCreate(
                    ['name' => $b['name'], 'type' => 'beat', 'parent_id' => $parent_id],
                    [
                        'km_start' => $b['km_start'] ?? null,
                        'km_end' => $b['km_end'] ?? null,
                        'phone' => $b['phone'] ?? null,
                        'short_name' => $b['short_name'] ?? null,
                        'is_active' => true,
                    ]
                );
            }

            $this->command->info('Offices seeded successfully from JSON (IG HQ as Root).');
        } else {
            $this->command->error('Offices JSON data not found!');
        }
    }
}
