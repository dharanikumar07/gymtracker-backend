<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_histories', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->uuid('uuid')->unique();
            $blueprint->uuid('user_uuid')->nullable()->index();
            $blueprint->string('job_name')->index();
            $blueprint->string('queue')->default('default');
            $blueprint->string('status')->default('pending')->index(); // pending, running, completed, failed
            $blueprint->json('payload')->nullable();
            $blueprint->text('failure_reason')->nullable();
            $blueprint->timestamp('started_at')->nullable();
            $blueprint->timestamp('completed_at')->nullable();
            $blueprint->timestamps();
            $blueprint->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_histories');
    }
};
