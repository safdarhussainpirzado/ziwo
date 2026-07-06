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
        Schema::disableForeignKeyConstraints();

        // 1. Agent configuration and tokens
        Schema::create('telephony_agent_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('ziwo_username', 150)->nullable();
            $table->text('ziwo_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('agent_status', 50)->default('offline'); // online, offline, pause, ringing, speaking
            $table->timestamp('last_status_change_at')->nullable();
            $table->timestamps();
        });

        // 2. Call history and recording links
        Schema::create('telephony_call_logs', function (Blueprint $table) {
            $table->id();
            $table->string('call_id', 100)->nullable()->unique();
            $table->string('call_uuid', 100)->nullable()->unique();
            $table->foreignId('agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('caller_number', 50);
            $table->string('direction', 20)->default('inbound'); // inbound, outbound
            $table->string('status', 50)->default('ringing'); // ringing, active, held, finished, missed
            $table->text('recording_url')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->json('metadata')->nullable(); // Holds custom state details (hold count, mute state, conferences)
            $table->timestamps();

            $table->index(['agent_id', 'status']);
        });

        // 3. Centralized searchable phonebook
        Schema::create('phonebook_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('phone_number', 50);
            $table->string('category', 50)->default('custom'); // beat, sector, zone, emergency, custom
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_favorite')->default(false);
            $table->json('metadata')->nullable(); // Optional properties (e.g. beat/sector parent, notes)
            $table->timestamps();

            $table->index('category');
            $table->index('name');
            $table->index('phone_number');
        });

        // 4. Webhook tracking for audits, retry, and monitoring
        Schema::create('telephony_webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 100);
            $table->json('payload');
            $table->boolean('processed')->default(false);
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('event_type');
            $table->index('processed');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('telephony_webhook_logs');
        Schema::dropIfExists('phonebook_contacts');
        Schema::dropIfExists('telephony_call_logs');
        Schema::dropIfExists('telephony_agent_configs');
        Schema::enableForeignKeyConstraints();
    }
};
