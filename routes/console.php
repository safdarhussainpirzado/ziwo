<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Add next month's audit_log partition on the 25th of each month at 02:00
// Ensures partitions are ready before the month begins (no runtime ALTER needed)
Schedule::command('audit:add-partition')->monthlyOn(25, '02:00');
