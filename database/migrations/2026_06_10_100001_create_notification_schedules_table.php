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
        Schema::create('notification_schedules', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->uuid('uuid')->unique();
            $blueprint->uuid('user_uuid')->index();
            $blueprint->string('module')->index(); // workout, expense
            $blueprint->time('reminder_time')->index();
            $blueprint->unsignedTinyInteger('reminder_count')->default(1);
            $blueprint->boolean('is_active')->default(true)->index();
            $blueprint->timestamps();
            $blueprint->softDeletes()->index();

            // Prevent duplicate schedule entries per user + module + time
            $blueprint->unique(['user_uuid', 'module', 'reminder_time'], 'ns_user_module_time_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_schedules');
    }
};
