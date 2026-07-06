<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NHMPCategorySeeder extends Seeder
{
    public function run(): void
    {
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        $now = Carbon::now();

        // 1. Vehicle Types with premium icons and colors
        $vehicleTypes = [
            ['name' => 'Car', 'icon' => 'fa-car', 'color_hex' => '#3b82f6'],
            ['name' => 'Motorcycle', 'icon' => 'fa-motorcycle', 'color_hex' => '#ef4444'],
            ['name' => 'Bus', 'icon' => 'fa-bus', 'color_hex' => '#10b981'],
            ['name' => 'Truck', 'icon' => 'fa-truck', 'color_hex' => '#f59e0b'],
            ['name' => 'Oil Tanker', 'icon' => 'fa-oil-can', 'color_hex' => '#64748b'],
            ['name' => 'Trailer / Container', 'icon' => 'fa-truck-moving', 'color_hex' => '#7c3aed'],
            ['name' => 'Pickup / Van', 'icon' => 'fa-truck-pickup', 'color_hex' => '#06b6d4'],
            ['name' => 'Bike Carrier', 'icon' => 'fa-bicycle', 'color_hex' => '#fbbf24'],
            ['name' => 'Haice / Van', 'icon' => 'fa-shuttle-van', 'color_hex' => '#0891b2'],
            ['name' => 'Mazda / Mini Truck', 'icon' => 'fa-truck-front', 'color_hex' => '#6366f1'],
            ['name' => 'Pickup', 'icon' => 'fa-truck-pickup', 'color_hex' => '#14b8a6'],
            ['name' => 'Tractor', 'icon' => 'fa-tractor', 'color_hex' => '#84cc16'],
            ['name' => 'Other Vehicle', 'icon' => 'fa-car', 'color_hex' => '#000000'],
        ];

        foreach ($vehicleTypes as $idx => $vt) {
            DB::table('vehicle_types')->updateOrInsert(
                ['name' => $vt['name']],
                [
                    'icon' => $vt['icon'],
                    'color_hex' => $vt['color_hex'],
                    'sort_order' => $idx + 1,
                    'is_active' => true,
                    'updated_at' => $now
                ]
            );
        }

        // 2. Call Types & Sub Types with accurate NHMP operational data
        $callTypes = [
            [
                'name' => 'Emergency',
                'category' => 'emergency',
                'priority' => 1,
                'icon' => 'fa-bolt-lightning',
                'color_hex' => '#C0392B',
                'sub_types' => [
                    ['name' => 'Accident', 'icon' => 'fa-car-burst'],
                    ['name' => 'Incident', 'icon' => 'fa-car-on'],
                    ['name' => 'Vehicle Fire', 'icon' => 'fa-fire'],
                    ['name' => 'Medical Emergency', 'icon' => 'fa-truck-medical'],
                    ['name' => 'Other Emergency', 'icon' => 'fa-circle-exclamation'],
                    
                ]
            ],
            [
                'name' => 'General Help',
                'category' => 'general_help',
                'priority' => 3,
                'icon' => 'fa-screwdriver-wrench',
                'color_hex' => '#d97706',
                'sub_types' => [
                    ['name' => 'Mechanical Fault', 'icon' => 'fa-gears'],
                    ['name' => 'Electrical Fault', 'icon' => 'fa-bolt'],
                    ['name' => 'Tyre Burst', 'icon' => 'fa-circle-dot'],
                    ['name' => 'Tyre Puncture', 'icon' => 'fa-circle-notch'],
                    ['name' => 'Out of Fuel', 'icon' => 'fa-gas-pump'],
                     ['name' => 'Wheel Jam', 'icon' => 'fa-circle-stop'],
                    ['name' => 'Oil Depletion', 'icon' => 'fa-fill-drip'],
                    ['name' => 'Engine Overheating', 'icon' => 'fa-temperature-high'],
                    ['name' => 'Heat-up', 'icon' => 'fa-fire-flame-simple'],
                    ['name' => 'Radiator Problem', 'icon' => 'fa-snowflake'],
                    ['name' => 'Battery Problem', 'icon' => 'fa-car-battery'],
                    ['name' => 'Starting Problem', 'icon' => 'fa-key'],
                    ['name' => 'Tool Assistance', 'icon' => 'fa-wrench'],
                    ['name' => 'Other Help', 'icon' => 'fa-hand-holding-heart'],
                   
                ]
            ],
            [
                'name' => 'Information',
                'category' => 'information',
                'priority' => 4,
                'icon' => 'fa-circle-info',
                'color_hex' => '#0284c7',
                'sub_types' => [
                    ['name' => 'Road Condition', 'icon' => 'fa-road'],
                    ['name' => 'Route Information', 'icon' => 'fa-route'],
                    ['name' => 'Weather Conditions', 'icon' => 'fa-cloud-showers-heavy'],
                    ['name' => 'Other Information', 'icon' => 'fa-info-circle']
                ]
            ],
            [
                'name' => 'Complaint',
                'category' => 'complaint',
                'priority' => 2,
                'icon' => 'fa-handshake-slash',
                'color_hex' => '#7c3aed',
                'sub_types' => [
                    ['name' => 'Reckless Driving', 'icon' => 'fa-gauge-high'],
                    ['name' => 'Overspeeding', 'icon' => 'fa-tachograph-digital'],
                    ['name' => 'Lane Violation', 'icon' => 'fa-road-spikes'],
                    ['name' => 'Officer Misconduct', 'icon' => 'fa-user-nurse'],
                    ['name' => 'Misbehavior', 'icon' => 'fa-users-viewfinder'],
                    ['name' => 'Overcharging', 'icon' => 'fa-money-bill-transfer'],
                    ['name' => 'Overloading', 'icon' => 'fa-truck-ramp-box'],
                    ['name' => 'Miscommitment', 'icon' => 'fa-handshake-slash'],
                    ['name' => 'Road Blockade', 'icon' => 'fa-road-circle-exclamation'],
                    ['name' => 'Animal Crossing', 'icon' => 'fa-cow'],
                    ['name' => 'Other Complaint', 'icon' => 'fa-thumbs-down']
                ]
            ],
            [
                'name' => 'Crime',
                'category' => 'emergency',
                'priority' => 1,
                'icon' => 'fa-shield-halved',
                'color_hex' => '#4b5563',
                'sub_types' => [
                    ['name' => 'Robbery / Snatching', 'icon' => 'fa-user-ninja'],
                    ['name' => 'Suspicious Activity', 'icon' => 'fa-eye'],
                    ['name' => 'Armed Firing', 'icon' => 'fa-fire-flame-dotted'],
                    ['name' => 'Stone Pelting', 'icon' => 'fa-mountain-sun'],
                    ['name' => 'Other Crime', 'icon' => 'fa-circle-exclamation']
                ]
            ],
            [
                'name' => 'Junk/Silent',
                'category' => 'junk_silent',
                'priority' => 5,
                'icon' => 'fa-microphone-slash',
                'color_hex' => '#94a3b8',
                'sub_types' => [
                    ['name' => 'Junk', 'icon' => 'fa-trash-can'],
                    ['name' => 'Silent', 'icon' => 'fa-volume-mute']
                ]
            ],
        ];

        // 3. Purge existing categories and sub-types not in this set (to keep DB clean)
        $names = array_column($callTypes, 'name');
        DB::table('call_types')->whereNotIn('name', $names)->delete();

        foreach ($callTypes as $idx => $ct) {
            $subTypes = $ct['sub_types'];
            unset($ct['sub_types']);

            $ct['sort_order'] = $idx + 1;
            $ct['is_active'] = true;
            $ct['created_at'] = $now;
            $ct['updated_at'] = $now;

            $typeId = DB::table('call_types')->where('name', $ct['name'])->value('id');
            if ($typeId) {
                DB::table('call_types')->where('id', $typeId)->update($ct);
            } else {
                $typeId = DB::table('call_types')->insertGetId($ct);
            }

            // Clean existing sub-types for this category to avoid duplicates/stale data
            $stNames = array_column($subTypes, 'name');
            DB::table('call_sub_types')->where('call_type_id', $typeId)->whereNotIn('name', $stNames)->delete();

            // Sub-types
            foreach ($subTypes as $stIdx => $st) {
                DB::table('call_sub_types')->updateOrInsert(
                    ['call_type_id' => $typeId, 'name' => $st['name']],
                    [
                        'icon' => $st['icon'],
                        'priority' => $ct['priority'],
                        'is_active' => true,
                        'sort_order' => $stIdx + 1,
                        'updated_at' => $now
                    ]
                );
            }
        }

        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();
    }
}
