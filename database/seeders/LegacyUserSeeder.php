<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Designation;
use App\Models\Office;
use App\Models\Role;

class LegacyUserSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Migrating users from Legacy DB...');
        
        try {
            $source = DB::connection('mysql_source');
        } catch (\Exception $e) {
            $this->command->error('Legacy DB connection failed: ' . $e->getMessage());
            return;
        }

        $legacyUsers = $source->table('users')->get();
        $defaultRoleId = Role::where('name', 'agent')->value('id');

        foreach ($legacyUsers as $lu) {
            if ($lu->username === 'nhmp_admin' || $lu->cnic === '00000-0000000-0') continue;
            // Find designation by short_code from legacy DB if possible
            $legacyDesignation = $source->table('designations')->where('id', $lu->designation_id)->first();
            $designationId = null;
            if ($legacyDesignation) {
                $designationId = Designation::where('short_code', $legacyDesignation->short_code)->value('id');
            }

            // Create/Update user
            $user = User::updateOrCreate(
                ['username' => $lu->username],
                [
                    'full_name' => $lu->full_name,
                    'email' => $lu->email ?? ($lu->username . '@nhmp.gov.pk'),
                    'mobile_no' => $lu->mobile_no ?? '00000000000',
                    'cnic' => $lu->cnic ?? '00000-0000000-0',
                    'password' => $lu->password, // Carry over hashed password
                    'role_id' => $lu->role_id ?? $defaultRoleId,
                    'designation_id' => $designationId,
                    'is_active' => $lu->is_active ?? true,
                    'created_at' => $lu->created_at,
                    'updated_at' => $lu->updated_at,
                ]
            );

            // Sync Scopes if possible
            // Note: Scopes might need mapping if they were Region/Zone/Sector/Beat specific
            // Since we unified offices, we might need to find the new office_id
            // $this->migrateScopes($user, $lu, $source);
        }

        $this->command->info('User migration complete.');
    }

    private function migrateScopes($user, $lu, $source)
    {
        $legacyScopes = $source->table('user_scopes')->where('user_id', $lu->id)->get();
        
        foreach ($legacyScopes as $ls) {
            $office = null;
            if ($ls->unit_type === 'call_centre') {
                $office = Office::where('type', 'call_center')->first();
            } elseif ($ls->unit_id) {
                $type = $ls->unit_type ?? 'beat'; 
                // Special mapping for legacy 'national' or other types if needed
                if ($type === 'national') {
                    $office = Office::where('type', 'plhq')->first();
                } else {
                    $legacyUnit = $source->table($type . 's')->where('id', $ls->unit_id)->first();
                    if ($legacyUnit) {
                        $office = Office::where('type', $type)->where('name', $legacyUnit->name)->first();
                    }
                }
            }

            DB::table('user_scopes')->updateOrInsert(
                ['user_id' => $user->id, 'office_id' => $office?->id],
                [
                    'access_level' => $ls->access_level ?? 'read_only',
                    'is_active' => $ls->is_active ?? true,
                    'created_at' => $ls->created_at ?? now(),
                    'updated_at' => $ls->updated_at ?? now(),
                ]
            );
        }
    }
}
