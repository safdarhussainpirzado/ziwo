<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestingFlowSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting Testing Flow Seeder...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->updateRoles();
        $this->createUsers();

        $this->command->info('Testing Flow Seeder completed successfully!');

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->displayTestCredentials();
    }

    private function updateRoles(): void
    {
        $this->command->info('Updating roles...');

        DB::table('roles')->delete();

        $roles = [
            ['id' => 1, 'name' => 'super_admin',      'display_name' => 'Super Administrator',    'scope_level' => 'national'],
            ['id' => 2, 'name' => 'operation_admin',   'display_name' => 'Operation Admin',         'scope_level' => 'national'],
            ['id' => 3, 'name' => 'zone_admin',        'display_name' => 'Zone Administrator',      'scope_level' => 'zone'],
            ['id' => 4, 'name' => 'sector_admin',      'display_name' => 'Sector Administrator',    'scope_level' => 'sector'],
            ['id' => 5, 'name' => 'beat_operator',     'display_name' => 'Beat Wireless Operator',  'scope_level' => 'beat'],
            ['id' => 6, 'name' => 'agent_supervisor',  'display_name' => 'Call Center Supervisor',  'scope_level' => 'call_centre'],
            ['id' => 7, 'name' => 'agent',             'display_name' => 'Call Center Agent',       'scope_level' => 'call_centre'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insert(array_merge($role, [
                'status'     => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Roles updated successfully.');
    }

    private function createUsers(): void
    {
        $this->command->info('Creating user accounts...');

        $roleIds   = DB::table('roles')->pluck('id', 'name');
        $desigIds  = DB::table('designations')->pluck('id', 'short_code');

        // username => [role, designation_short_code, cnic, full_name, email, mobile, scope_type, scope_id]
        $users = [
            'zone4_admin'     => [$roleIds['zone_admin'],       'SP',     '35202-1234567-1', 'Muhammad Akram',  'zone4.admin@nhmp.gov.pk',    '03001234567', 'zone',       4],
            'sector13_admin'  => [$roleIds['sector_admin'],     'DSP/CPO','35202-1234567-2', 'Imran Shah',      'sector13.admin@nhmp.gov.pk', '03001234568', 'sector',     13],
            'sector14_admin'  => [$roleIds['sector_admin'],     'DSP/CPO','35202-1234567-3', 'Rashid Mehmood',  'sector14.admin@nhmp.gov.pk', '03001234569', 'sector',     14],
            'beat44_admin'    => [$roleIds['beat_operator'],    'IP/SPO', '35202-1234567-4', 'Tariq Aziz',      'beat44.admin@nhmp.gov.pk',   '03001234570', 'beat',       44],
            'beat45_admin'    => [$roleIds['beat_operator'],    'IP/SPO', '35202-1234567-5', 'Nasir Khan',      'beat45.admin@nhmp.gov.pk',   '03001234571', 'beat',       45],
            'beat48_admin'    => [$roleIds['beat_operator'],    'IP/SPO', '35202-1234567-6', 'Farooq Ahmed',    'beat48.admin@nhmp.gov.pk',   '03001234572', 'beat',       48],
            'beat50_admin'    => [$roleIds['beat_operator'],    'IP/SPO', '35202-1234567-7', 'Saeed Akhtar',    'beat50.admin@nhmp.gov.pk',   '03001234573', 'beat',       50],
            'supervisor'      => [$roleIds['agent_supervisor'], 'IP/SPO', '35202-1234567-8', 'Shahid Iqbal',    'supervisor@nhmp.gov.pk',     '03001234574', 'call_centre', 1],
            'agent1'          => [$roleIds['agent'],            'C/JPO',  '35202-1234567-9', 'Ahmed Raza',      'agent1@nhmp.gov.pk',         '03001234575', 'call_centre', 1],
            'agent2'          => [$roleIds['agent'],            'HC/APO', '35202-1234568-0', 'Bilal Hassan',    'agent2@nhmp.gov.pk',         '03001234576', 'call_centre', 1],
            'agent3'          => [$roleIds['agent'],            'C/JPO',  '35202-1234568-1', 'Danish Tariq',    'agent3@nhmp.gov.pk',         '03001234577', 'call_centre', 1],
        ];

        foreach ($users as $username => [$roleId, $desigCode, $cnic, $fullName, $email, $mobile, $scopeType, $scopeId]) {
            $desigId = $desigIds[$desigCode] ?? null;

            DB::table('users')->updateOrInsert(
                ['username' => $username],
                [
                    'designation_id' => $desigId,
                    'cnic'           => $cnic,
                    'full_name'      => $fullName,
                    'email'          => $email,
                    'mobile_no'      => $mobile,
                    'password'       => Hash::make('NHMPAdmin@2026!'),
                    'role_id'        => $roleId,
                    'is_active'      => true,
                    'failed_attempts'=> 0,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]
            );

            $userId = DB::table('users')->where('username', $username)->value('id');

            // Find valid office_id for this scope
            $officeId = null;
            if ($scopeType !== 'national' && $scopeType !== 'call_centre') {
                $officeId = DB::table('offices')->where('type', $scopeType)->where('id', $scopeId)->value('id');
                // Fallback to first office of this type if specific ID not found
                if (!$officeId) {
                    $officeId = DB::table('offices')->where('type', $scopeType)->value('id');
                }
            }

            // Agents share call_centre scope (represented by NULL or a specific root office if needed)
            // In the new schema, national/call_centre level might just have office_id = NULL
            DB::table('user_scopes')->updateOrInsert(
                ['user_id' => $userId, 'office_id' => $officeId],
                ['access_level' => 'full', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        $this->command->info(count($users) . ' user accounts and scopes created.');
    }

    private function displayTestCredentials(): void
    {
        $this->command->newLine();
        $this->command->info('═══════════════════════════════════════════════════════');
        $this->command->info('              TEST CREDENTIALS SUMMARY');
        $this->command->info('═══════════════════════════════════════════════════════');

        $this->command->table(
            ['Role Description', 'Username', 'Password'],
            [
                ['Zone 4 Admin',           'zone4_admin',    'NHMPAdmin@2026!'],
                ['Sector 13 Admin',        'sector13_admin', 'NHMPAdmin@2026!'],
                ['Sector 14 Admin',        'sector14_admin', 'NHMPAdmin@2026!'],
                ['Beat 44 Operator',       'beat44_admin',   'NHMPAdmin@2026!'],
                ['Beat 45 Operator',       'beat45_admin',   'NHMPAdmin@2026!'],
                ['Beat 48 Operator',       'beat48_admin',   'NHMPAdmin@2026!'],
                ['Beat 50 Operator',       'beat50_admin',   'NHMPAdmin@2026!'],
                ['Call Center Supervisor', 'supervisor',     'NHMPAdmin@2026!'],
                ['Call Center Agent 1',    'agent1',         'NHMPAdmin@2026!'],
                ['Call Center Agent 2',    'agent2',         'NHMPAdmin@2026!'],
                ['Call Center Agent 3',    'agent3',         'NHMPAdmin@2026!'],
            ]
        );

        $this->command->info('═══════════════════════════════════════════════════════');
    }
}