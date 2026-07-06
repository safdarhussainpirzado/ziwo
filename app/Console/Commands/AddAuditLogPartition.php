<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Artisan command to add the next month's partition to audit_logs.
 *
 * Add to crontab / Laravel scheduler:
 *   $schedule->command('audit:add-partition')->monthlyOn(25, '02:00');
 *
 * Runs on the 25th of each month to prepare next month's partition in advance.
 */
class AddAuditLogPartition extends Command
{
    protected $signature   = 'audit:add-partition {--month= : Target month YYYYMM (default: next month)}';
    protected $description = 'Add next month\'s partition to audit_logs table (run monthly via scheduler)';

    public function handle(): int
    {
        if ($this->option('month')) {
            $target = \Carbon\Carbon::createFromFormat('Ym', $this->option('month'));
        } else {
            $target = now()->addMonth()->startOfMonth();
        }

        $partitionName  = 'p' . $target->format('Ym');              // e.g. p202507
        $nextMonthStart = $target->copy()->addMonth()->startOfMonth()->toDateString(); // upper bound

        // Check if partition already exists
        $exists = DB::select("
            SELECT PARTITION_NAME
            FROM information_schema.PARTITIONS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'audit_logs'
              AND PARTITION_NAME = ?
        ", [$partitionName]);

        if (!empty($exists)) {
            $this->info("Partition {$partitionName} already exists. Nothing to do.");
            return Command::SUCCESS;
        }

        // Add the new partition before the p_future catchall
        DB::unprepared("
            ALTER TABLE audit_logs
            REORGANIZE PARTITION p_future INTO (
                PARTITION {$partitionName} VALUES LESS THAN (UNIX_TIMESTAMP('{$nextMonthStart} 00:00:00')),
                PARTITION p_future VALUES LESS THAN MAXVALUE
            )
        ");

        $this->info("✅ Added partition {$partitionName} covering up to {$nextMonthStart}");
        return Command::SUCCESS;
    }
}
