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
        Schema::create('physical_activity_tracker', function (Blueprint $table) {
            $table->id();

            $table->uuid('user_uuid');
            $table->uuid('slot_uuid');

            $table->date('activity_date');

            $table->integer('year')->nullable();

            $table->jsonb('metrics_data')->nullable();

            $table->timestamps();

            $table->index('user_uuid');
            $table->index('slot_uuid');
            $table->index('activity_date');

            $table->foreign('user_uuid')
                ->references('uuid')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('slot_uuid')
                ->references('uuid')
                ->on('physical_activity_slots')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('physical_activity_tracker');
    }
};
