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
        Schema::create('notification_logs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->uuid('uuid')->unique();
            $blueprint->uuid('user_uuid')->index();
            $blueprint->string('module')->index(); // workout, expense
            $blueprint->date('notification_date')->index();
            $blueprint->time('notification_time')->index();
            $blueprint->timestamp('actual_notification_time_sended')->nullable();
            $blueprint->string('status')->default('pending')->index(); // completed, skipped, failed
            $blueprint->text('failure_reason')->nullable();
            $blueprint->timestamps();
            $blueprint->softDeletes()->index();

            // Prevent duplicate logs per user + module + date + time
            $blueprint->unique(
                ['user_uuid', 'module', 'notification_date', 'notification_time'],
                'nl_user_module_date_time_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
