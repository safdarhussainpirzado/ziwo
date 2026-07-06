<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DataMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds for legacy data migration.
     * This seeder imports a SQL dump file incrementally.
     */
    public function run(): void
    {
        // 1. Get the filename from .env or default to the one provided by user
        $filename = env('LEGACY_DUMP_FILE', 'Dump20260502_legacy.sql');
        $filePath = base_path($filename);

        if (!File::exists($filePath)) {
            $this->command->error("Legacy dump file not found: {$filePath}");
            $this->command->info("Please place the file in the project root or set LEGACY_DUMP_FILE in .env");
            return;
        }

        $this->command->info("Starting incremental migration from: {$filename}");
        $this->command->warn("Note: This seeder uses 'INSERT IGNORE' to prevent duplicates and handle missing data incrementally.");

        // 2. Disable foreign key checks for the import
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            $this->importSqlFile($filePath);
            $this->command->info("Migration completed successfully!");
        } catch (\Exception $e) {
            $this->command->error("Migration failed: " . $e->getMessage());
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    /**
     * Imports a SQL file by streaming it and executing statements.
     * Only processes 'INSERT' statements to avoid overwriting the new schema.
     */
    private function importSqlFile(string $path): void
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            throw new \Exception("Could not open file: {$path}");
        }

        $sqlBatch = '';
        $batchSize = 0;
        $maxBatchSize = 1024 * 1024; // 1MB batches

        while (($line = fgets($handle)) !== false) {
            $trimmedLine = trim($line);
            
            // SECURITY/INTEGRITY: Only allow INSERT statements.
            // This prevents the dump from dropping our new tables or changing schema.
            if (!str_ireplace(' ', '', $trimmedLine) || stripos($trimmedLine, 'INSERT INTO') === false) {
                continue;
            }

            // Incremental logic: Convert INSERT to INSERT IGNORE
            $line = str_ireplace('INSERT INTO', 'INSERT IGNORE INTO', $line);

            $sqlBatch .= $line;
            $batchSize += strlen($line);

            // Execute batch if it ends with a semicolon and reached size limit
            if (str_ends_with(trim($line), ';') && $batchSize >= $maxBatchSize) {
                try {
                    DB::unprepared($sqlBatch);
                    $this->command->comment("Imported a data batch...");
                } catch (\Exception $e) {
                    $this->command->warn("Skipping a batch due to error: " . $e->getMessage());
                }
                $sqlBatch = '';
                $batchSize = 0;
            }
        }

        // Execute remaining SQL
        if (!empty(trim($sqlBatch))) {
            try {
                DB::unprepared($sqlBatch);
                $this->command->comment("Imported final data batch...");
            } catch (\Exception $e) {
                $this->command->warn("Skipping final batch due to error: " . $e->getMessage());
            }
        }

        fclose($handle);
    }
}
