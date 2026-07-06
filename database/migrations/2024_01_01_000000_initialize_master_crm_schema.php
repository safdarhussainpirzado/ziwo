<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration {
    /**
     * Run the migrations.
     * Consolidates all crm schema updates into a single master initialization.
     * Updated for Revamped Project Structure (Consolidated Users & Cleaned up Legacy)
     */
    public function up(): void {
        Schema::disableForeignKeyConstraints();

        /* ── 1. Geography & Infrastructure ───────────────────────────── */
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name', 150);
            $table->enum('type', ['plhq', 'region', 'zone', 'sector', 'beat', 'call_center', 'hq', 'unit'])->index();
            $table->foreignId('parent_id')->nullable()->constrained('offices')->cascadeOnDelete();
            $table->enum('operational_type', ['field', 'office'])->default('field');
            $table->boolean('is_active')->default(true);
            $table->decimal('km_start', 10, 2)->nullable();
            $table->decimal('km_end', 10, 2)->nullable();
            $table->string('phone', 100)->nullable();
            $table->string('short_name', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['type', 'is_active']);
            $table->index('parent_id');
        });

        Schema::create('carriageways', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['Motorway', 'Highway', 'Strategic Route'])->default('Motorway');
            $table->string('road', 10);
            $table->string('road_short', 10)->nullable();
            $table->string('road_name', 100);
            $table->string('road_from', 50)->nullable();
            $table->string('road_to', 50)->nullable();
            $table->decimal('total_km', 8, 2)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->unique(['type', 'road']);
        });

        /* ── 2. Personnel & Identity ─────────────────────────────────── */
        Schema::create('designations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('short_code', 20);
            $table->unsignedTinyInteger('bps')->nullable();
            $table->string('similar_rank')->nullable();
            $table->enum('type', ['Uniform', 'Non-Uniform'])->default('Uniform');
            $table->unsignedTinyInteger('sort_order')->default(99);
            $table->boolean('is_field')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /* ── 3. Roles, Permissions & Scopes ──────────────────────────── */
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->string('display_name', 100);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('scope_level', ['national', 'region', 'zone', 'sector', 'beat', 'call_centre']);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('display_name', 150);
            $table->string('module', 60);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index('module');
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 150);
            $table->string('email', 100)->unique();
            $table->string('mobile_no', 15)->unique();
            $table->string('cnic', 15)->unique()->nullable();
            $table->foreignId('designation_id')->nullable()->constrained('designations');
            $table->string('username', 80)->unique();
            $table->string('password');
            $table->string('remember_token', 100)->nullable();
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('failed_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->string('totp_secret', 100)->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->timestamps();
        });

        Schema::create('user_scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('office_id')->nullable()->constrained('offices');
            $table->enum('access_level', ['read_only', 'read_write', 'full'])->default('read_only');
            $table->string('label', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['user_id', 'office_id'], 'user_scope_unique');
            $table->index(['user_id', 'is_active'], 'user_scope_lookup');
        });

        /* ── 4. Operational Lookup Data ──────────────────────────────── */
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name', 80);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        DB::table('languages')->insert([
            ['code' => 'ur', 'name' => 'Urdu', 'is_default' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'en', 'name' => 'English', 'is_default' => false, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'pa', 'name' => 'Punjabi', 'is_default' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::create('call_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('icon', 100)->nullable();
            $table->enum('category', ['emergency', 'general_help', 'complaint', 'information', 'junk_silent']);
            $table->unsignedTinyInteger('priority')->default(3);
            $table->string('color_hex', 7)->default('#3498DB');
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(99);
            $table->timestamps();
        });

        Schema::create('call_sub_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('call_type_id')->constrained('call_types');
            $table->string('name', 100);
            $table->string('icon', 100)->nullable();
            $table->unsignedTinyInteger('priority')->default(3);
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(99);
            $table->timestamps();
            $table->unique(['call_type_id', 'name']);
        });

        Schema::create('vehicle_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('icon', 100)->nullable();
            $table->string('color_hex', 7)->default('#3498DB');
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('sort_order')->default(99);
            $table->timestamps();
        });

        /* ── 5. Core Operational Data (Calls) ────────────────────────── */
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->string('call_number', 30)->unique();
            $table->foreignId('agent_id')->constrained('users');
            $table->string('caller_number', 20);
            $table->string('caller_name', 150)->nullable();
            $table->boolean('is_reminder_call')->default(false);
            $table->unsignedInteger('call_reminder_count')->default(0);
            $table->timestamp('last_reminder_at')->nullable();
            $table->foreignId('parent_call_id')->nullable()->constrained('calls');
            $table->foreignId('call_type_id')->constrained('call_types');
            $table->foreignId('call_sub_type_id')->nullable()->constrained('call_sub_types');
            $table->text('details')->nullable();
            $table->text('location_details')->nullable();
            $table->foreignId('language_id')->nullable()->constrained('languages')->nullOnDelete();
            $table->boolean('is_phone_activity')->default(false);
            $table->foreignId('vehicle_type_id')->nullable()->constrained('vehicle_types');
            $table->string('vehicle_no', 30)->nullable();
            $table->foreignId('carriageway_id')->nullable()->constrained('carriageways');
            $table->string('km_marker_text', 20)->nullable();
            $table->foreignId('office_id')->nullable()->constrained('offices');
            $table->decimal('caller_lat', 10, 7)->nullable();
            $table->decimal('caller_lng', 10, 7)->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled', 'junk', 'forwarded'])->default('pending');
            $table->unsignedTinyInteger('priority')->default(3);
            $table->enum('forwarded_to_level', ['beat', 'sector', 'zone', 'hq'])->nullable();
            $table->foreignId('forwarded_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->timestamp('call_start_time')->nullable();
            $table->timestamp('call_pickup_time')->nullable();
            $table->timestamp('call_end_time')->nullable();
            $table->unsignedInteger('wait_time_seconds')->nullable();
            $table->unsignedInteger('agent_call_duration')->nullable();
            $table->unsignedInteger('response_time_sec')->nullable();
            $table->unsignedInteger('resolution_time_sec')->nullable();
            $table->text('pending_remarks')->nullable();
            $table->unsignedBigInteger('pending_status_by')->nullable();
            $table->text('inprogress_remarks')->nullable();
            $table->unsignedBigInteger('inprogress_status_by')->nullable();
            $table->timestamp('inprogress_at')->nullable();
            $table->text('completed_remarks')->nullable();
            $table->unsignedBigInteger('completed_status_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('cancelled_remarks')->nullable();
            $table->boolean('followup_needed')->default(false);
            $table->timestamps();
            $table->index('status');
        });

        Schema::create('call_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('call_id')->constrained('calls');
            $table->foreignId('changed_by')->constrained('users');
            $table->string('old_status', 30)->nullable();
            $table->string('new_status', 30);
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('officer_id')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        /* ── 6. System & Infrastructure ──────────────────────────────── */
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('action', ['create', 'update', 'delete', 'login', 'logout', 'login_failed', 'export']);
            $table->string('table_name', 80)->nullable();
            $table->unsignedBigInteger('record_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('tts_scripts', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100)->unique();
            $table->string('language', 30)->default('Urdu');
            $table->text('content');
            $table->string('audio_path')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('type', 80);
            $table->string('title', 200);
            $table->text('body')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();
            $table->string('related_type', 80)->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key_name', 100)->unique();
            $table->text('value')->nullable();
            $table->string('description')->nullable();
            $table->string('group_name', 80)->default('general');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('outgoing_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('call_id')->constrained('calls');
            $table->foreignId('made_by')->constrained('users');
            $table->string('called_no', 20);
            $table->string('reason')->nullable();
            $table->unsignedInteger('duration_sec')->nullable();
            $table->string('outcome')->nullable();
            $table->timestamp('called_at')->useCurrent();
        });

        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('login_at')->useCurrent();
            $table->timestamp('logout_at')->nullable();
        });

        Schema::create('password_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('password');
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
        
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        Schema::enableForeignKeyConstraints();

        /* ── 7. Performance Optimizations (Partitioning & Triggers) ───── */

        // Audit Log Partitioning
        $this->partitionAuditLogs();

        // Audit Log WORM Triggers
        $this->createAuditWormTriggers();
    }

    private function partitionAuditLogs(): void {
        $partitions = $this->generatePartitions(2026, 1, 18);
        $partitionSql = implode(",\n", $partitions);

        DB::unprepared("
            ALTER TABLE audit_logs
            DROP PRIMARY KEY,
            ADD PRIMARY KEY (id, created_at)
        ");

        DB::unprepared("
            ALTER TABLE audit_logs
            PARTITION BY RANGE (UNIX_TIMESTAMP(created_at)) (
                {$partitionSql}
            )
        ");
    }

    private function generatePartitions(int $startYear, int $startMonth, int $count): array {
        $partitions = [];
        $year = $startYear;
        $month = $startMonth;

        for ($i = 0; $i < $count; $i++) {
            $nextMonth = $month === 12 ? 1 : $month + 1;
            $nextYear = $month === 12 ? $year + 1 : $year;
            $nextMonthFirstDay = sprintf('%04d-%02d-01', $nextYear, $nextMonth);
            $partitionName = sprintf('p%04d%02d', $year, $month);
            $partitions[] = "PARTITION {$partitionName} VALUES LESS THAN (UNIX_TIMESTAMP('{$nextMonthFirstDay} 00:00:00'))";
            $month = $nextMonth;
            $year = $nextYear;
        }

        $partitions[] = "PARTITION p_future VALUES LESS THAN MAXVALUE";
        return $partitions;
    }

    private function createAuditWormTriggers(): void {
        try {
            DB::unprepared("
                DROP TRIGGER IF EXISTS audit_logs_no_update;
                CREATE TRIGGER audit_logs_no_update
                    BEFORE UPDATE ON audit_logs FOR EACH ROW
                    BEGIN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'audit_logs is append-only: UPDATE not permitted';
                    END;
            ");

            DB::unprepared("
                DROP TRIGGER IF EXISTS audit_logs_no_delete;
                CREATE TRIGGER audit_logs_no_delete
                    BEFORE DELETE ON audit_logs FOR EACH ROW
                    BEGIN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'audit_logs is append-only: DELETE not permitted';
                    END;
            ");
        } catch (\Exception $e) {
            Log::warning('Migration: Could not create Audit Log WORM triggers due to missing SUPER privileges or log_bin_trust_function_creators=0. Audit logs will not be append-only at the database level.');
        }
    }

    public function down(): void {
        Schema::disableForeignKeyConstraints();
        $tables = [
            'password_histories', 'login_logs', 'outgoing_calls', 'system_settings', 'notifications', 'tts_scripts', 'audit_logs',
            'call_status_history', 'calls', 'vehicle_types', 'call_sub_types', 'call_types', 'user_scopes', 'users', 'role_permissions', 'permissions', 'roles', 
            'designations', 'offices', 'carriageways',
            'languages', 'sessions', 'cache', 'cache_locks', 'jobs', 'failed_jobs', 'job_batches'
        ];
        foreach($tables as $table) { if ($table) Schema::dropIfExists($table); }
        Schema::enableForeignKeyConstraints();
    }
};
