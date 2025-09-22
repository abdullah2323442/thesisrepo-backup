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
        Schema::create('group_panel_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->foreignId('supervisor_id')->references('id')->on('supervisors')->onDelete('cascade');
            $table->timestamp('assigned_at')->useCurrent();
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamps();
            
            // Foreign key for assigned_by (admin who assigned the panel member)
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
            
            // Unique constraint to prevent duplicate assignments
            $table->unique(['group_id', 'supervisor_id']);
            
            // Indexes for performance
            $table->index('group_id');
            $table->index('supervisor_id');
            $table->index('assigned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_panel_members');
    }
};