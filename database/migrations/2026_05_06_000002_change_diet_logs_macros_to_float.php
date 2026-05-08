<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('diet_logs', function (Blueprint $table) {
            $table->float('calories', 53)->nullable()->change();
            $table->float('protein', 53)->nullable()->change();
            $table->float('carbs', 53)->nullable()->change();
            $table->float('fats', 53)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('diet_logs', function (Blueprint $table) {
            $table->smallInteger('calories')->nullable()->change();
            $table->smallInteger('protein')->nullable()->change();
            $table->smallInteger('carbs')->nullable()->change();
            $table->smallInteger('fats')->nullable()->change();
        });
    }
};
