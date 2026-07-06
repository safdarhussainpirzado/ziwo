<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * RolesAndPermissionsSeeder
 * ──────────────────────────────────────────────────────────────────────────
 * Seeds:
 *   • roles            – 7 system roles
 *   • permissions      – granular permission slugs grouped by module
 *   • role_permissions – maps each role to exactly the permissions it owns
 *
 * Permission Naming Convention:  <module>.<action>[_<qualifier>]
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        /* ================================================================
         * 1. ROLES
         * ================================================================ */
        $roles = [
            ['id' => 1, 'name' => 'super_admin',       'display_name' => 'Super Administrator',    'scope_level' => 'national'],
            ['id' => 2, 'name' => 'operation_admin',   'display_name' => 'Operation Admin',         'scope_level' => 'national'],
            ['id' => 3, 'name' => 'zone_admin',         'display_name' => 'Zone Administrator',     'scope_level' => 'zone'],
            ['id' => 4, 'name' => 'sector_admin',       'display_name' => 'Sector Administrator',   'scope_level' => 'sector'],
            ['id' => 5, 'name' => 'beat_operator',      'display_name' => 'Beat Wireless Operator', 'scope_level' => 'beat'],
            ['id' => 6, 'name' => 'agent_supervisor',   'display_name' => 'Call Center Supervisor', 'scope_level' => 'call_centre'],
            ['id' => 7, 'name' => 'agent',              'display_name' => 'Call Center Agent',      'scope_level' => 'call_centre'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['id'   => $role['id']],
                array_merge($role, ['created_at' => $now, 'updated_at' => $now])
            );
        }

        /* ================================================================
         * 2. PERMISSIONS
         * ================================================================ */
        $permissions = [
            /* ── DASHBOARD module ────────────────────────────────────── */
            [
                'name'         => 'dashboard.view',
                'display_name' => 'View System Dashboard',
                'module'       => 'dashboard',
                'description'  => 'Access to the main analytics dashboard.',
            ],

            /* ── CALLS module ─────────────────────────────────────────── */
            [
                'name'         => 'calls.view',
                'display_name' => 'View Calls',
                'module'       => 'calls',
                'description'  => 'Permission to browse call listings.',
            ],
            [
                'name'         => 'calls.create',
                'display_name' => 'Create Call',
                'module'       => 'calls',
                'description'  => 'Agent can log a new call.',
            ],
            [
                'name'         => 'calls.update',
                'display_name' => 'Update Call',
                'module'       => 'calls',
                'description'  => 'Update call details or add remarks.',
            ],
            [
                'name'         => 'calls.delete',
                'display_name' => 'Delete Call',
                'module'       => 'calls',
                'description'  => 'Hard delete a call record.',
            ],
            [
                'name'         => 'calls.manage_status',
                'display_name' => 'Manage Call Status',
                'module'       => 'calls',
                'description'  => 'Transition calls between pending, in-progress, and completed states.',
            ],
            [
                'name'         => 'calls.export',
                'display_name' => 'Export Calls',
                'module'       => 'calls',
                'description'  => 'Export call data to CSV/Excel.',
            ],
            [
                'name'         => 'calls.api_lookup',
                'display_name' => 'Use Operational Lookups',
                'module'       => 'calls',
                'description'  => 'Access to AJAX lookups.',
            ],

            /* ── REPORTS module ───────────────────────────────────────── */
            [
                'name'         => 'reports.view',
                'display_name' => 'View Reports Module',
                'module'       => 'reports',
                'description'  => 'Access to the main reports interface.',
            ],
            [
                'name'         => 'reports.call_type_summary',
                'display_name' => 'Report: Call Type Summary',
                'module'       => 'reports',
                'description'  => 'View call classification reports.',
            ],
            [
                'name'         => 'reports.beat_wise',
                'display_name' => 'Report: Beat-wise Analytics',
                'module'       => 'reports',
                'description'  => 'View incident reports grouped by beats.',
            ],
            [
                'name'         => 'reports.agent_wise',
                'display_name' => 'Report: Agent Performance',
                'module'       => 'reports',
                'description'  => 'View agent efficiency and call handling stats.',
            ],
            [
                'name'         => 'reports.sla_compliance',
                'display_name' => 'Report: SLA Compliance',
                'module'       => 'reports',
                'description'  => 'View response time and SLA metrics.',
            ],
            [
                'name'         => 'reports.max_response_time',
                'display_name' => 'Report: Max Response Time',
                'module'       => 'reports',
                'description'  => 'View reports on delayed responses.',
            ],
            [
                'name'         => 'reports.predictive_analysis',
                'display_name' => 'Report: Predictive Analysis',
                'module'       => 'reports',
                'description'  => 'Access advanced predictive reports.',
            ],

            /* ── USERS module ─────────────────────────────────────────── */
            [
                'name'         => 'users.view',
                'display_name' => 'View Users',
                'module'       => 'users',
                'description'  => 'Browse the system user list.',
            ],
            [
                'name'         => 'users.create',
                'display_name' => 'Create User',
                'module'       => 'users',
                'description'  => 'Create new system users.',
            ],
            [
                'name'         => 'users.update',
                'display_name' => 'Update User',
                'module'       => 'users',
                'description'  => 'Update user profiles.',
            ],
            [
                'name'         => 'users.delete',
                'display_name' => 'Delete User',
                'module'       => 'users',
                'description'  => 'Remove user accounts.',
            ],
            [
                'name'         => 'users.manage_status',
                'display_name' => 'Toggle User Status',
                'module'       => 'users',
                'description'  => 'Activate or deactivate user accounts.',
            ],
            [
                'name'         => 'users.manage_password',
                'display_name' => 'Reset User Passwords',
                'module'       => 'users',
                'description'  => 'Reset passwords for any user.',
            ],
            [
                'name'         => 'users.manage_scopes',
                'display_name' => 'Manage Dynamic Scopes',
                'module'       => 'users',
                'description'  => 'Assign multiple geographic scopes.',
            ],

            /* ── ROLES & PERMISSIONS module ───────────────────────────── */
            [
                'name'         => 'roles.view',
                'display_name' => 'View Roles',
                'module'       => 'security',
                'description'  => 'View defined system roles.',
            ],
            [
                'name'         => 'roles.create',
                'display_name' => 'Create Role',
                'module'       => 'security',
                'description'  => 'Define new roles.',
            ],
            [
                'name'         => 'roles.update',
                'display_name' => 'Update Role',
                'module'       => 'security',
                'description'  => 'Modify existing roles.',
            ],
            [
                'name'         => 'roles.delete',
                'display_name' => 'Delete Role',
                'module'       => 'security',
                'description'  => 'Purge roles.',
            ],
            [
                'name'         => 'permissions.view',
                'display_name' => 'View Permissions',
                'module'       => 'security',
                'description'  => 'View the granular capability registry.',
            ],
            [
                'name'         => 'permissions.create',
                'display_name' => 'Create Permission',
                'module'       => 'security',
                'description'  => 'Define new granular permissions.',
            ],
            [
                'name'         => 'permissions.update',
                'display_name' => 'Update Permission',
                'module'       => 'security',
                'description'  => 'Modify existing permission definitions.',
            ],
            [
                'name'         => 'permissions.delete',
                'display_name' => 'Delete Permission',
                'module'       => 'security',
                'description'  => 'Purge permissions from the system.',
            ],

            /* ── GEOGRAPHY module ─────────────────────────────────────── */
            [
                'name'         => 'geography.offices.view',
                'display_name' => 'View Operational Units',
                'module'       => 'geography',
                'description'  => 'Browse regions, zones, sectors, and beats.',
            ],
            [
                'name'         => 'geography.offices.create',
                'display_name' => 'Create Operational Unit',
                'module'       => 'geography',
                'description'  => 'Add new geographic offices.',
            ],
            [
                'name'         => 'geography.offices.update',
                'display_name' => 'Update Operational Unit',
                'module'       => 'geography',
                'description'  => 'Modify bounds of operational units.',
            ],
            [
                'name'         => 'geography.offices.delete',
                'display_name' => 'Delete Operational Unit',
                'module'       => 'geography',
                'description'  => 'Remove geographic units.',
            ],
            [
                'name'         => 'geography.carriageways.view',
                'display_name' => 'View Highway Registry',
                'module'       => 'geography',
                'description'  => 'Browse motorways and highways.',
            ],
            [
                'name'         => 'geography.carriageways.manage',
                'display_name' => 'Manage Highway Registry',
                'module'       => 'geography',
                'description'  => 'Create, update, or delete highway segments.',
            ],
            [
                'name'         => 'geography.geospatial.view',
                'display_name' => 'View Geospatial Markers',
                'module'       => 'geography',
                'description'  => 'Browse landmarks and map markers.',
            ],
            [
                'name'         => 'geography.geospatial.manage',
                'display_name' => 'Manage Geospatial Markers',
                'module'       => 'geography',
                'description'  => 'Create, update, or delete map landmarks.',
            ],

            /* ── SYSTEM module ────────────────────────────────────────── */
            [
                'name'         => 'system.audit_view',
                'display_name' => 'View Audit Logs',
                'module'       => 'system',
                'description'  => 'Inspect system logs.',
            ],
            [
                'name'         => 'system.settings.view',
                'display_name' => 'View System Settings',
                'module'       => 'system',
                'description'  => 'Read core system configuration.',
            ],
            [
                'name'         => 'system.settings.update',
                'display_name' => 'Update System Settings',
                'module'       => 'system',
                'description'  => 'Modify core system configuration.',
            ],
            [
                'name'         => 'system.tts_scripts.manage',
                'display_name' => 'Manage TTS Scripts',
                'module'       => 'system',
                'description'  => 'Create and delete text-to-speech scripts.',
            ],

            /* ── PROFILE module ────────────────────────────────────────── */
            [
                'name'         => 'profile.update',
                'display_name' => 'Update Profile',
                'module'       => 'profile',
                'description'  => 'Update personal account settings.',
            ],
            [
                'name'         => 'profile.self_manage',
                'display_name' => 'Self Management',
                'module'       => 'profile',
                'description'  => 'Manage own profile details.',
            ],
            [
                'name'         => 'profile.password_change',
                'display_name' => 'Password Change',
                'module'       => 'profile',
                'description'  => 'Change own password.',
            ],
        ];

        foreach ($permissions as $perm) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $perm['name']],
                array_merge($perm, ['created_at' => $now, 'updated_at' => $now])
            );
        }

        /* ================================================================
         * 3. ROLE → PERMISSIONS MAP
         * ================================================================ */
        $permId = fn(string $name): int => (int) DB::table('permissions')->where('name', $name)->value('id');
        $roleId = fn(string $name): int => (int) DB::table('roles')->where('name', $name)->value('id');

        $rolePermissions = [
            'agent' => [
                'calls.create',
                'calls.view',
                'calls.api_lookup',
                'profile.update',
            ],
            'beat_operator' => [
                'calls.view',
                'calls.manage_status',
                'calls.update',
                'calls.api_lookup',
                'calls.export',
                'profile.update',
            ],
            'sector_admin' => [
                'dashboard.view',
                'calls.view',
                'reports.view',
                'reports.beat_wise',
                'reports.call_type_summary',
                'calls.export',
                'profile.update',
            ],
            'zone_admin' => [
                'dashboard.view',
                'calls.view',
                'reports.view',
                'reports.beat_wise',
                'reports.call_type_summary',
                'reports.sla_compliance',
                'calls.export',
                'profile.update',
            ],
            'agent_supervisor' => [
                'dashboard.view',
                'calls.view',
                'calls.export',
                'reports.view',
                'reports.agent_wise',
                'reports.call_type_summary',
                'reports.sla_compliance',
                'profile.update',
            ],
            'operation_admin' => [
                'dashboard.view',
                'calls.view',
                'calls.export',
                'users.view',
                'users.create',
                'users.update',
                'users.manage_status',
                'users.manage_password',
                'users.manage_scopes',
                'geography.offices.view',
                'geography.offices.create',
                'geography.offices.update',
                'geography.carriageways.view',
                'geography.carriageways.manage',
                'geography.geospatial.view',
                'geography.geospatial.manage',
                'reports.view',
                'system.settings.view',
                'profile.update',
                'profile.self_manage',
                'profile.password_change',
            ],
            'super_admin' => array_column($permissions, 'name'),
        ];

        foreach ($rolePermissions as $roleName => $permNames) {
            $rId = $roleId($roleName);
            DB::table('role_permissions')->where('role_id', $rId)->delete();
            $rows = array_map(fn($p) => ['role_id' => $rId, 'permission_id' => $permId($p)], $permNames);
            DB::table('role_permissions')->insert($rows);
        }

        $this->command->info('✔ Roles & permissions seeded successfully.');
    }
}
