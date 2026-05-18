<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expense_logs', function (Blueprint $table) {
            $table->uuid('plan_cycle_uuid')->nullable()->after('user_uuid');
            $table->foreign('plan_cycle_uuid')->references('uuid')->on('budget_plan_cycles')->onDelete('cascade');
            $table->index('plan_cycle_uuid');
        });
    }

    public function down(): void
    {
        Schema::table('expense_logs', function (Blueprint $table) {
            $table->dropForeign(['plan_cycle_uuid']);
            $table->dropColumn('plan_cycle_uuid');
        });
    }
};
