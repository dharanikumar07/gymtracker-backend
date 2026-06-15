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
        Schema::create('user_device_tokens', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->uuid('uuid')->unique();
            $blueprint->uuid('user_uuid')->index();
            $blueprint->string('fcm_token', 500);
            $blueprint->string('device_type')->default('web'); // web, android, ios
            $blueprint->boolean('is_active')->default(true)->index();
            $blueprint->timestamps();
            $blueprint->softDeletes()->index();

            // One token per device
            $blueprint->unique(['fcm_token'], 'udt_fcm_token_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_device_tokens');
    }
};
