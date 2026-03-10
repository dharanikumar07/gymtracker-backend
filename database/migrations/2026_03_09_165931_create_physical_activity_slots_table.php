<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('physical_activity_slots', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->uuid('user_uuid');
            $table->uuid('plan_uuid');
            $table->string('exercise_name');
            $table->integer('exercise_order');
            $table->enum('day', [
                'mon',
                'tue',
                'wed',
                'thu',
                'fri',
                'sat',
                'sun'
            ]);

            $table->string('metrics_type')->nullable();
            $table->jsonb('metrics_data')->nullable();
            $table->jsonb('meta_data')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_uuid')
                ->references('uuid')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('plan_uuid')
                ->references('uuid')
                ->on('plans')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('physical_activity_slots');
    }
};
