<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_plan_cycles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('user_uuid');
            $table->uuid('plan_uuid');
            
            $table->date('cycle_start');
            $table->date('cycle_end');
            
            $table->integer('budget_amount');
            $table->integer('fixed_expense_total')->default(0);
            $table->integer('variable_expense_total')->default(0);
            $table->integer('remaining_amount')->default(0);
            
            $table->string('status')->default('active'); // active, paused, completed, terminated
            $table->jsonb('meta_data')->nullable();
            
            $table->timestamps();

            $table->foreign('user_uuid')->references('uuid')->on('users')->onDelete('cascade');
            $table->foreign('plan_uuid')->references('uuid')->on('plans')->onDelete('cascade');
            
            $table->index('user_uuid');
            $table->index('plan_uuid');
            $table->index(['cycle_end', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_plan_cycles');
    }
};
