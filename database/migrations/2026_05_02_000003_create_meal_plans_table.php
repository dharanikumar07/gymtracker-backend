<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('meal_plans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->uuid('user_uuid');
            $table->uuid('plan_uuid');

            $table->string('meal_name');
            $table->enum('day', ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun']);
            $table->enum('time_period', ['breakfast', 'lunch', 'dinner', 'snack']);

            $table->jsonb('food_data');

            $table->smallInteger('calories')->nullable();
            $table->smallInteger('protein')->nullable();
            $table->smallInteger('carbs')->nullable();
            $table->smallInteger('fats')->nullable();

            $table->jsonb('nutrition_data')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_uuid')->references('uuid')->on('users')->cascadeOnDelete();
            $table->foreign('plan_uuid')->references('uuid')->on('plans')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_plans');
    }
};
