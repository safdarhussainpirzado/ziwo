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
        Schema::create('geospatial_markers', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('office_id')
                  ->constrained('offices')
                  ->cascadeOnDelete()
                  ->comment('References the Beat-level office');

            $table->decimal('km', 9, 2)->index();
            $table->double('lat', 12, 8);
            $table->double('lng', 12, 8);
            $table->string('side', 2)->nullable()->comment('N, S, E, W, etc.');
            
            $table->timestamps();

            $table->index(['office_id', 'km']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('geospatial_markers');
    }
};
