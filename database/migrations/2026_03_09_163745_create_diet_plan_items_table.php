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
        Schema::create('diet_plan_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->uuid('user_uuid');
            $table->uuid('plan_uuid');

            $table->enum('meal_type', ['breakfast', 'lunch', 'dinner', 'snack'])->nullable();
            $table->enum('day', ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'])->nullable();

            $table->string('food_name')->nullable();
            $table->decimal('quantity', 8, 2);
            $table->enum('unit', [
                'g',
                'kg',
                'ml',
                'l',
                'pcs',
                'cup'
            ])->nullable();

            $table->smallInteger('calories')->nullable();
            $table->smallInteger('protein')->nullable();
            $table->smallInteger('carbs')->nullable();
            $table->smallInteger('fats')->nullable();

            $table->jsonb('nutrition_data')->nullable();

            $table->timestamps();
            $table->softDeletes();

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
        Schema::dropIfExists('diet_plan_items');
    }
};
