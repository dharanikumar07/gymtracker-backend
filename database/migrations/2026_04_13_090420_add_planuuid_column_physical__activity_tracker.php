<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('physical_activity_tracker', function (Blueprint $table) {
            $table->string('plan_uuid')->after('slot_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('physical_activity_tracker', function (Blueprint $table) {

            if (Schema::hasColumn('physical_activity_tracker', 'type')) {
                $table->dropColumn('type');
            }

        });
    }
};
