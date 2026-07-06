<?php
// Script to generate comparison report
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== MIGRATION COMPARISON REPORT ===\n\n";

$legacyCalls = DB::table('legacy_calls')->count();
$newCalls = DB::table('calls')->count();
echo "Total Calls: Legacy ($legacyCalls) vs Migrated ($newCalls)\n";

$legacySubTypes = DB::table('legacy_call_sub_types')->count();
$newSubTypes = DB::table('call_sub_types')->count();
echo "Total Sub Types: Legacy ($legacySubTypes) vs Migrated ($newSubTypes)\n";

// Zone/Sector/Beat distribution
$legacyZones = DB::table('legacy_zones')->count();
$newZones = DB::table('offices')->where('type', 'zone')->count();
echo "Zones: Legacy ($legacyZones) vs Migrated ($newZones)\n";

$legacySectors = DB::table('legacy_sectors')->count();
$newSectors = DB::table('offices')->where('type', 'sector')->count();
echo "Sectors: Legacy ($legacySectors) vs Migrated ($newSectors)\n";

$legacyBeats = DB::table('legacy_beats')->count();
$newBeats = DB::table('offices')->where('type', 'beat')->count();
echo "Beats: Legacy ($legacyBeats) vs Migrated ($newBeats)\n\n";

echo "=== MIGRATED CATEGORY DISTRIBUTION ===\n";
$dist = DB::table('calls')
    ->join('call_sub_types', 'calls.call_sub_type_id', '=', 'call_sub_types.id')
    ->join('call_types', 'calls.call_type_id', '=', 'call_types.id')
    ->select('call_types.name as type', 'call_sub_types.name as subtype', DB::raw('count(*) as total'))
    ->groupBy('call_types.name', 'call_sub_types.name')
    ->orderBy('call_types.name')
    ->orderBy('call_sub_types.name')
    ->get();

foreach ($dist as $row) {
    echo "{$row->type} -> {$row->subtype}: {$row->total}\n";
}

echo "\nReport generated successfully.\n";
