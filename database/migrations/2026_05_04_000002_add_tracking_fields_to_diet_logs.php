<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('diet_logs', function (Blueprint $table) {
            $table->string('type')->nullable()->after('day');
            $table->string('status')->default('completed')->after('type');
            $table->string('reason')->nullable()->after('status');
            $table->dropColumn('notes');
        });
    }

    public function down(): void
    {
        Schema::table('diet_logs', function (Blueprint $table) {
            $table->dropColumn(['type', 'status', 'reason']);
            $table->string('notes')->nullable()->after('fats');
        });
    }
};
