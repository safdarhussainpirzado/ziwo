<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    const CALLS_TABLE = 'calls';
    const CALL_SUB_TYPE_COL = 'call_sub_type_id';
    const CALL_TYPE_COL = 'call_type_id';
    const VEHICLE_TYPE_COL = 'vehicle_type_id';

    /**
     * Run the migrations.
     * Maps legacy categories, vehicle types, and HEIRARCHICAL OFFICES with date sanitization.
     */
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::statement("SET SESSION sql_mode = '';");

        // 1. Move data from staging to real tables with OFFICE mapping and DATE sanitization
        $this->migrateDesignationsFromStaging();
        $this->migrateCallsFromStaging();
        $this->migrateHistoryFromStaging();
        $this->migrateUsersFromStaging();
        $this->migrateUserScopesFromStaging();
        $this->migrateAuditLogsFromStaging();

        // 2. Build Category mappings and update IDs
        $subMapping = $this->buildCallSubTypeMapping();
        $vehMapping = $this->buildVehicleTypeMapping();
        $this->updateCallsTable($subMapping, $vehMapping);

        // 4. Convert all timestamps from GMT to PKT (GMT+5)
        // This includes DDL (Trigger management) which causes implicit commits
        $this->convertTimestampsToPkt();

        $this->printMigrationReport();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function printMigrationReport(): void
    {
        $this->command()->info("\n=== MIGRATION COMPARISON REPORT ===");
        
        $legacyCalls = DB::table('legacy_calls')->count();
        $newCalls = DB::table('calls')->count();
        $this->command()->info("Total Calls: Legacy ($legacyCalls) vs Migrated ($newCalls)");

        $legacyZones = DB::table('legacy_zones')->count();
        $newZones = DB::table('offices')->where('type', 'zone')->count();
        $this->command()->info("Zones: Legacy ($legacyZones) vs Migrated ($newZones)");

        $legacySectors = DB::table('legacy_sectors')->count();
        $newSectors = DB::table('offices')->where('type', 'sector')->count();
        $this->command()->info("Sectors: Legacy ($legacySectors) vs Migrated ($newSectors)");

        $legacyBeats = DB::table('legacy_beats')->count();
        $newBeats = DB::table('offices')->where('type', 'beat')->count();
        $this->command()->info("Beats: Legacy ($legacyBeats) vs Migrated ($newBeats)");

        $this->command()->info("\n=== CATEGORY DISTRIBUTION ===");
        $dist = DB::table('calls')
            ->join('call_sub_types', 'calls.call_sub_type_id', '=', 'call_sub_types.id')
            ->join('call_types', 'calls.call_type_id', '=', 'call_types.id')
            ->select('call_types.name as type', 'call_sub_types.name as subtype', DB::raw('count(*) as total'))
            ->groupBy('call_types.name', 'call_sub_types.name')
            ->orderBy('call_types.name')
            ->orderBy('call_sub_types.name')
            ->get();

        foreach ($dist as $row) {
            $this->command()->info("{$row->type} -> {$row->subtype}: {$row->total}");
        }
        $this->command()->info("===================================\n");
    }

    private function migrateCallsFromStaging(): void
    {
        if (!Schema::hasTable('legacy_calls')) {
            return;
        }

        $this->command()->info('Migrating calls from staging (Sanitizing dates and mapping offices)...');

        // Map beat_id to new office_id by matching ZONE and BEAT names
        // This is more robust as Sector names have changed or been subdivided
        DB::statement("
            INSERT INTO calls (
                id, call_number, agent_id, caller_number, caller_name, is_reminder_call, 
                call_reminder_count, last_reminder_at, parent_call_id, call_type_id, 
                call_sub_type_id, details, location_details, language_id, is_phone_activity, 
                vehicle_type_id, vehicle_no, carriageway_id, km_marker_text, office_id, 
                caller_lat, caller_lng, status, priority, forwarded_to_level, 
                forwarded_to_user_id, rating, call_start_time, call_pickup_time, 
                call_end_time, wait_time_seconds, agent_call_duration, response_time_sec, 
                resolution_time_sec, pending_remarks, pending_status_by, inprogress_remarks, 
                inprogress_status_by, inprogress_at, completed_remarks, completed_status_by, 
                completed_at, cancelled_remarks, followup_needed, created_at, updated_at
            )
            SELECT 
                lc.id, lc.call_number, lc.agent_id, lc.caller_number, lc.caller_name, lc.is_reminder_call,
                lc.call_reminder_count, 
                NULLIF(lc.last_reminder_at, '0000-00-00 00:00:00'), 
                lc.parent_call_id, lc.call_type_id,
                lc.call_sub_type_id, lc.details, lc.location_details, lc.language_id, lc.is_phone_activity,
                lc.vehicle_type_id, lc.vehicle_no, lc.carriageway_id, lc.km_marker_text,
                COALESCE(
                    (
                        -- Strategy 1: Match by Zone Name and Beat Name (Most reliable)
                        SELECT o_beat.id 
                        FROM offices o_beat
                        JOIN offices o_sector ON o_beat.parent_id = o_sector.id
                        JOIN offices o_zone ON o_sector.parent_id = o_zone.id
                        JOIN legacy_beats lb ON lb.id = lc.beat_id
                        JOIN legacy_sectors ls ON lb.sector_id = ls.id
                        JOIN legacy_zones lz ON ls.zone_id = lz.id
                        WHERE o_beat.name = lb.name 
                        AND o_zone.name = lz.name
                        AND o_beat.type = 'beat' AND o_zone.type = 'zone'
                        LIMIT 1
                    ),
                    (
                        -- Strategy 2: Match by Beat Name only (Fallback)
                        SELECT o.id FROM offices o 
                        JOIN legacy_beats lb ON o.name = lb.name 
                        WHERE lb.id = lc.beat_id AND o.type = 'beat' 
                        LIMIT 1
                    ),
                    NULL -- Fallback to NULL if unmapped
                ),
                lc.caller_lat, lc.caller_lng, lc.status, lc.priority, lc.forwarded_to_level,
                lc.forwarded_to_user_id, lc.rating, 
                NULLIF(lc.call_start_time, '0000-00-00 00:00:00'), 
                NULLIF(lc.call_pickup_time, '0000-00-00 00:00:00'), 
                NULLIF(lc.call_end_time, '0000-00-00 00:00:00'), 
                lc.wait_time_seconds, lc.agent_call_duration, lc.response_time_sec,
                lc.resolution_time_sec, lc.pending_remarks, lc.pending_status_by, lc.inprogress_remarks,
                lc.inprogress_status_by, 
                NULLIF(lc.inprogress_at, '0000-00-00 00:00:00'), 
                lc.completed_remarks, lc.completed_status_by, 
                NULLIF(lc.completed_at, '0000-00-00 00:00:00'), 
                lc.cancelled_remarks, lc.followup_needed, 
                COALESCE(NULLIF(lc.created_at, '0000-00-00 00:00:00'), NOW()), 
                COALESCE(NULLIF(lc.updated_at, '0000-00-00 00:00:00'), NOW())
            FROM legacy_calls lc
        ");
    }

    private function migrateHistoryFromStaging(): void
    {
        if (Schema::hasTable('legacy_call_status_history')) {
            $this->command()->info('Migrating history from staging (Sanitizing dates)...');
            DB::statement("
                INSERT IGNORE INTO call_status_history (id, call_id, changed_by, old_status, new_status, remarks, officer_id, created_at)
                SELECT id, call_id, changed_by, old_status, new_status, remarks, officer_id, NULLIF(created_at, '0000-00-00 00:00:00')
                FROM legacy_call_status_history
            ");
        }
    }

    private function migrateDesignationsFromStaging(): void
    {
        if (Schema::hasTable('legacy_designations')) {
            $this->command()->info('Mapping designations from legacy data...');
            // Insert names that don't exist in the new table
            DB::statement("
                INSERT IGNORE INTO designations (name, short_code, type, created_at, updated_at)
                SELECT name, COALESCE(short_code, name), 'Uniform', NOW(), NOW()
                FROM legacy_designations
                WHERE name NOT IN (SELECT name FROM designations)
            ");
        }
    }

    private function migrateUsersFromStaging(): void
    {
        if (Schema::hasTable('legacy_users')) {
            $this->command()->info('Migrating users from staging (Sanitizing dates and mapping designations)...');
            
            // Map designations by name to ensure IDs are correct in the new system
            DB::statement("
                INSERT IGNORE INTO users (
                    id, full_name, email, mobile_no, cnic, designation_id, username, 
                    password, role_id, is_active, failed_attempts, locked_until, 
                    last_login_at, created_at, updated_at
                )
                SELECT 
                    lu.id, lu.full_name, lu.email, lu.mobile_no, lu.cnic, 
                    COALESCE(
                        (SELECT d.id FROM designations d 
                         JOIN legacy_designations ld ON d.name = ld.name 
                         WHERE ld.id = lu.designation_id LIMIT 1),
                        lu.designation_id -- Fallback to legacy ID if no name match (not ideal but better than null if IDs happened to match)
                    ) as mapped_designation_id,
                    lu.username, lu.password, lu.role_id, lu.is_active, lu.failed_attempts, 
                    NULLIF(lu.locked_until, '0000-00-00 00:00:00'), 
                    NULLIF(lu.last_login_at, '0000-00-00 00:00:00'), 
                    NULLIF(lu.created_at, '0000-00-00 00:00:00'), 
                    NULLIF(lu.updated_at, '0000-00-00 00:00:00')
                FROM legacy_users lu
            ");
        }
    }

    private function migrateUserScopesFromStaging(): void
    {
        if (Schema::hasTable('legacy_user_scopes')) {
            $this->command()->info('Migrating user scopes from staging...');
            
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::statement("
                INSERT IGNORE INTO user_scopes (
                    id, user_id, office_id, access_level, label, is_active, created_at, updated_at
                )
                SELECT 
                    lus.id, lus.user_id,
                    COALESCE(
                        CASE 
                            WHEN lus.unit_type = 'call_centre' THEN (SELECT id FROM offices WHERE type = 'call_center' LIMIT 1)
                            WHEN lus.unit_type = 'national' THEN 1
                            WHEN lus.unit_type = 'beat' THEN (
                                SELECT o.id FROM offices o 
                                JOIN legacy_beats lb ON o.name = lb.name 
                                WHERE lb.id = lus.unit_id AND o.type = 'beat' LIMIT 1
                            )
                            WHEN lus.unit_type = 'sector' THEN (
                                SELECT o.id FROM offices o 
                                JOIN legacy_sectors ls ON o.name = ls.name 
                                WHERE ls.id = lus.unit_id AND o.type = 'sector' LIMIT 1
                            )
                            WHEN lus.unit_type = 'zone' THEN (
                                SELECT o.id FROM offices o 
                                JOIN legacy_zones lz ON o.name = lz.name 
                                WHERE lz.id = lus.unit_id AND o.type = 'zone' LIMIT 1
                            )
                            ELSE 1
                        END,
                        1 -- Fallback to PLHQ if unmapped
                    ) as mapped_office_id,
                    lus.access_level, 
                    lus.unit_type as label, 
                    lus.is_active, 
                    NULLIF(lus.created_at, '0000-00-00 00:00:00'), 
                    NULLIF(lus.updated_at, '0000-00-00 00:00:00')
                FROM legacy_user_scopes lus
            ");
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    private function migrateAuditLogsFromStaging(): void
    {
        if (Schema::hasTable('legacy_audit_logs')) {
            $this->command()->info('Migrating audit logs from staging...');
            DB::statement("
                INSERT IGNORE INTO audit_logs (
                    id, user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent, created_at
                )
                SELECT 
                    id, user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent, 
                    NULLIF(created_at, '0000-00-00 00:00:00')
                FROM legacy_audit_logs
            ");
        }
    }

    private function backupOldTables(): void
    {
        $tables = ['call_types', 'call_sub_types', 'vehicle_types'];
        foreach ($tables as $table) {
            $backup = $table . '_backup';
            if (!Schema::hasTable($backup)) {
                DB::statement("CREATE TABLE $backup LIKE $table");
                DB::statement("INSERT $backup SELECT * FROM $table");
            }
        }
    }

    private function buildCallSubTypeMapping(): array
    {
        $nameOverrides = [
            'Animal/Object on road' => 'Animal Crossing',
            'Animal on Road'        => 'Animal Crossing',
            'Road Closure'          => 'Road Blockade',
            'Officer Misbehavior'   => 'Officer Misconduct',
            'Overheating'           => 'Engine Overheating',
            'Radiator Issue'        => 'Radiator Problem',
            'Tools Required'        => 'Tool Assistance',
            'Tyre Burst / Puncture' => 'Tyre Puncture',
            'Over-speeding'         => 'Overspeeding',
            'Motorway Status'       => 'Road Condition',
            'Route / Direction'     => 'Route Information',
            'Weather / Fog'         => 'Weather Conditions',
        ];

        $movedToComplaint = ['Animal on Road', 'Road Closure', 'Lane Violation', 'Misbehave', 'Reckless Driving'];

        $mapping = [];
        $complaintTypeId = DB::table('call_types')->where('name', 'Complaint')->value('id');

        if (!Schema::hasTable('legacy_call_sub_types')) {
            return [];
        }

        $oldSubs = DB::table('legacy_call_sub_types as os')
            ->join('legacy_call_types as ct', 'ct.id', '=', 'os.call_type_id')
            ->select('os.id as old_id', 'os.name as old_name', 'ct.name as old_call_type')
            ->get();

        foreach ($oldSubs as $old) {
            $newName = $nameOverrides[$old->old_name] ?? $old->old_name;
            $query = DB::table('call_sub_types')->where('name', 'like', $newName);

            if (in_array($old->old_name, $movedToComplaint)) {
                $query = $query->where('call_type_id', $complaintTypeId);
            }

            $newId = $query->value('id');
            if ($newId) {
                $mapping[$old->old_id] = [
                    'new_id'        => $newId,
                    'old_call_type' => $old->old_call_type,
                ];
            }
        }
        return $mapping;
    }

    private function buildVehicleTypeMapping(): array
    {
        $mapping = [];
        if (!Schema::hasTable('legacy_vehicle_types')) {
            return [];
        }
        $oldVehicles = DB::table('legacy_vehicle_types')->get();
        foreach ($oldVehicles as $old) {
            $newId = DB::table('vehicle_types')->where('name', $old->name)->value('id');
            if ($newId) {
                $mapping[$old->id] = $newId;
            }
        }
        return $mapping;
    }

    private function updateCallsTable(array $subMapping, array $vehMapping): void
    {
        if (!Schema::hasTable(self::CALLS_TABLE)) return;

        // 1. Correct the main call_type_id by matching legacy type names with new type names
        DB::statement("
            UPDATE calls c
            JOIN legacy_calls lc ON c.id = lc.id
            JOIN legacy_call_types lct ON lc.call_type_id = lct.id
            JOIN call_types nct ON lct.name = nct.name
            SET c.call_type_id = nct.id
        ");

        // 2. Update sub-types and occasionally override call_type_id if moved to Complaint
        $complaintTypeId = DB::table('call_types')->where('name', 'Complaint')->value('id');

        foreach ($subMapping as $oldId => $data) {
            $newId = $data['new_id'];
            $newParentId = DB::table('call_sub_types')->where('id', $newId)->value('call_type_id');
            
            $setType = "";
            if ($newParentId == $complaintTypeId && Schema::hasColumn(self::CALLS_TABLE, self::CALL_TYPE_COL)) {
                $setType = ", c." . self::CALL_TYPE_COL . " = $complaintTypeId";
            }

            DB::statement("
                UPDATE calls c
                JOIN legacy_calls lc ON c.id = lc.id
                SET c." . self::CALL_SUB_TYPE_COL . " = $newId $setType
                WHERE lc." . self::CALL_SUB_TYPE_COL . " = $oldId
            ");
        }

        // 3. Update vehicle types safely
        if (Schema::hasColumn(self::CALLS_TABLE, self::VEHICLE_TYPE_COL)) {
            foreach ($vehMapping as $oldId => $newId) {
                DB::statement("
                    UPDATE calls c
                    JOIN legacy_calls lc ON c.id = lc.id
                    SET c." . self::VEHICLE_TYPE_COL . " = $newId
                    WHERE lc." . self::VEHICLE_TYPE_COL . " = $oldId
                ");
            }
        }
    }

    private function convertTimestampsToPkt(): void
    {
        $this->command()->warn('Converting legacy timestamps from GMT to PKT (GMT+5)...');

        // Temporarily drop triggers that prevent updates on audit_logs
        DB::statement("DROP TRIGGER IF EXISTS audit_logs_no_update");
        DB::statement("DROP TRIGGER IF EXISTS audit_logs_no_delete");

        $tables = [
            'calls' => ['created_at', 'updated_at', 'last_reminder_at', 'call_start_time', 'call_pickup_time', 'call_end_time', 'inprogress_at', 'completed_at'],
            'users' => ['created_at', 'updated_at', 'last_login_at', 'locked_until'],
            'audit_logs' => ['created_at'],
            'login_logs' => ['login_at', 'logout_at'],
            'outgoing_calls' => ['called_at'],
            'notifications' => ['created_at', 'read_at'],
            'call_status_history' => ['created_at'],
            'user_scopes' => ['created_at', 'updated_at']
        ];

        foreach ($tables as $table => $columns) {
            if (!Schema::hasTable($table)) continue;
            $updateParts = [];
            foreach ($columns as $column) {
                if (Schema::hasColumn($table, $column)) {
                    // We add 10 hours because mysqldump exported PKT as UTC (subtracting 5 hours),
                    // and the user explicitly requires the final database timestamps to be GMT+5 relative to the original text.
                    $updateParts[] = "{$column} = CASE WHEN {$column} IS NOT NULL THEN DATE_ADD({$column}, INTERVAL 10 HOUR) ELSE NULL END";
                }
            }
            if (!empty($updateParts)) {
                DB::statement("UPDATE {$table} SET " . implode(', ', $updateParts));
            }
        }

        // Recreate triggers to maintain append-only integrity
        DB::unprepared("
            CREATE TRIGGER audit_logs_no_update BEFORE UPDATE ON audit_logs
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'audit_logs is append-only: UPDATE not permitted';
            END
        ");
        DB::unprepared("
            CREATE TRIGGER audit_logs_no_delete BEFORE DELETE ON audit_logs
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'audit_logs is append-only: DELETE not permitted';
            END
        ");
    }

    public function down(): void {}
    
    private function command() {
        return new class {
            public function info($msg) { echo "\033[32m" . $msg . "\033[0m\n"; }
            public function warn($msg) { echo "\033[33m" . $msg . "\033[0m\n"; }
        };
    }
};
