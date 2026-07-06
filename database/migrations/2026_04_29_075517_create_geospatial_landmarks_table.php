<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('geospatial_landmarks', function (Blueprint $table) {
            $table->id();

            // ── Hierarchy FK ──────────────────────────────────────────────────────
            $table->foreignId('office_id')
                  ->constrained('offices')
                  ->cascadeOnDelete()
                  ->comment('References the Beat-level office');

            // ── Road / Location identifiers ───────────────────────────────────────
            $table->string('road_name')->index()
                  ->comment('e.g. M-1, M-2, N-5, E-35, KKH, N-25, N-10, N-50, N-55');

            $table->string('bound_direction')->nullable()
                  ->comment('e.g. North, South, North & South, Northbound, Southbound');

            $table->string('km_marker')->nullable()
                  ->comment('Raw KM label from source, e.g. 504, 24-N, 95-97, 355-354(S)');

            $table->decimal('km_numeric', 9, 2)->nullable()->index()
                  ->comment('Numeric KM for sorting and range queries (start of range if range given)');

            // ── Hierarchy context (denormalised for fast agent look-up) ────────────
            $table->string('zone_name')->nullable()->index()
                  ->comment('Denormalised Zone name, e.g. Motorway North, N5 South, West');

            $table->string('sector_name')->nullable()->index()
                  ->comment('Denormalised Sector name, e.g. M1, E35, South1, N10-Gwadar');

            $table->string('beat_name')->nullable()->index()
                  ->comment('Denormalised Beat name, e.g. Beat-01, Beat-01-IMDC');

            // ── Landmark details ──────────────────────────────────────────────────
            $table->string('location_name')->nullable()
                  ->comment('Primary landmark / location label, e.g. Jhari Kass Interchange');

            $table->string('nearby_cities')->nullable()
                  ->comment('Comma-separated nearby city / exit names');

            $table->string('exit_number')->nullable()
                  ->comment('Motorway exit number / label, e.g. 13 (Bhabra), 10 (Faisalabad, Multan) M-4');

            $table->string('fuel_station')->nullable()
                  ->comment('Fuel availability note, e.g. 1 KM, 500 Meters, Fuel Available');

            // ── Agent support fields ──────────────────────────────────────────────
            $table->text('agent_prompt')->nullable()
                  ->comment('Dynamic prompt for call-centre / IVR agent, auto-generated from location context');

            $table->string('contact_numbers')->nullable()
                  ->comment('Beat / Sector contact numbers extracted from source document');

            // ── Beat range helpers ────────────────────────────────────────────────
            $table->decimal('beat_km_start', 9, 2)->nullable()
                  ->comment('Starting KM of the beat patrol range');

            $table->decimal('beat_km_end', 9, 2)->nullable()
                  ->comment('Ending KM of the beat patrol range');

            $table->timestamps();

            // ── Composite indexes for common query patterns ───────────────────────
            $table->index(['road_name', 'km_numeric']);
            $table->index(['zone_name', 'sector_name', 'beat_name']);
            $table->index(['office_id', 'road_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geospatial_landmarks');
    }
};
