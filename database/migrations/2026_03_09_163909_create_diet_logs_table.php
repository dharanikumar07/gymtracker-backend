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
        Schema::create('diet_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->uuid('user_uuid');
            $table->uuid('plan_uuid');
            $table->uuid('diet_plan_item_uuid');
            $table->enum('day', [
                'mon',
                'tue',
                'wed',
                'thu',
                'fri',
                'sat',
                'sun'
            ]);

            $table->string('actual_food_name')->nullable();
            $table->decimal('actual_quantity', 8, 2);
            $table->enum('quantity_unit', [
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

            $table->string('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('user_uuid');
            $table->index('plan_uuid');
            $table->index('diet_plan_item_uuid');

            $table->foreign('user_uuid')
                ->references('uuid')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('plan_uuid')
                ->references('uuid')
                ->on('plans')
                ->cascadeOnDelete();

            $table->foreign('diet_plan_item_uuid')
                ->references('uuid')
                ->on('diet_plan_items')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diet_logs');
    }
};
