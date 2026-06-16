<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('diet_logs');
        Schema::dropIfExists('meal_plans');

        if (Schema::hasColumn('users', 'food_data')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('food_data');
            });
        }
    }

    public function down(): void
    {
        Schema::create('meal_plans', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('user_uuid')->index();
            $table->string('meal_type');
            $table->string('food_name');
            $table->string('day');
            $table->float('quantity')->default(0);
            $table->string('unit')->nullable();
            $table->float('calories')->default(0);
            $table->float('protein')->default(0);
            $table->float('carbs')->default(0);
            $table->float('fat')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('diet_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('user_uuid')->index();
            $table->string('meal_type');
            $table->string('planned_food_name')->nullable();
            $table->string('actual_food_name');
            $table->string('day');
            $table->date('activity_date')->nullable();
            $table->float('quantity')->default(0);
            $table->string('unit')->nullable();
            $table->float('calories')->default(0);
            $table->float('protein')->default(0);
            $table->float('carbs')->default(0);
            $table->float('fat')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        if (!Schema::hasColumn('users', 'food_data')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('food_data')->nullable()->after('user_fitness_data');
            });
        }
    }
};
