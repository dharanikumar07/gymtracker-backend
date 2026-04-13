<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('physical_activity_tracker', function (Blueprint $table) {
            $table->enum('day', [
                'mon',
                'tue',
                'wed',
                'thu',
                'fri',
                'sat',
                'sun'
            ])->after('year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('physical_activity_tracker', function (Blueprint $table) {
            if (Schema::hasColumn('physical_activity_tracker', 'day')) {
                $table->dropColumn('day');
            }
        });
    }
};
