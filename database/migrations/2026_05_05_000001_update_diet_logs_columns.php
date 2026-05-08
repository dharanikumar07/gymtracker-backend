<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('diet_logs', function (Blueprint $table) {
            $table->jsonb('food_data')->nullable()->after('day');
            $table->dropColumn(['actual_food_name', 'actual_quantity', 'quantity_unit']);
        });
    }

    public function down(): void
    {
        Schema::table('diet_logs', function (Blueprint $table) {
            $table->string('actual_food_name')->nullable()->after('day');
            $table->decimal('actual_quantity', 8, 2)->nullable()->after('actual_food_name');
            $table->string('quantity_unit')->nullable()->after('actual_quantity');
            $table->dropColumn('food_data');
        });
    }
};
