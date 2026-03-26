<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expense_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            
            $table->uuid('user_uuid');
            $table->foreign('user_uuid')->references('uuid')->on('users')->onDelete('cascade');
            
            $table->uuid('category_uuid');
            $table->foreign('category_uuid')->references('uuid')->on('expense_categories')->onDelete('cascade');
            
            $table->string('name'); // e.g. lunch, dosa, breakfast
            $table->integer('amount');
            $table->date('expense_date');
            $table->string('notes')->nullable();
            
            $table->timestamps();

            // Indexes
            $table->index('user_uuid');
            $table->index('category_uuid');
            $table->index('expense_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_logs');
    }
};
