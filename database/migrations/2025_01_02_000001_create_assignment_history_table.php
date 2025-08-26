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
        Schema::create('assignment_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('supervisor_id');
            $table->unsignedBigInteger('area_of_interest_id')->nullable();
            $table->string('assignment_method')->nullable(); // 'lottery', 'manual', etc.
            $table->integer('assignment_round')->default(1); // Track how many times this pairing occurred
            $table->timestamp('assigned_at');
            $table->timestamp('unassigned_at')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['group_id', 'supervisor_id']);
            $table->index('assigned_at');
            
            // Foreign keys
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $table->foreign('supervisor_id')->references('id')->on('supervisors')->onDelete('cascade');
            $table->foreign('area_of_interest_id')->references('id')->on('area_of_interests')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_history');
    }
};