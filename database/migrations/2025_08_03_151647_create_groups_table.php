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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('batch_number');
            $table->unsignedBigInteger('advisor_id')->nullable();
            $table->enum('created_by_type', ['admin', 'advisor'])->default('advisor');
            $table->unsignedBigInteger('created_by_admin_id')->nullable();
            $table->boolean('advisor_auto_detected')->default(false);
            $table->integer('max_students')->default(3);
            $table->unsignedBigInteger('area_of_interest_id')->nullable();
            $table->unsignedBigInteger('matched_area_of_interest_id')->nullable();
            $table->unsignedBigInteger('supervisor_id')->nullable();
            $table->boolean('is_manual_assignment')->default(false);
            $table->integer('assignment_priority')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('advisor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by_admin_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('area_of_interest_id')->references('id')->on('area_of_interests')->onDelete('set null');
            $table->foreign('matched_area_of_interest_id')->references('id')->on('area_of_interests')->onDelete('set null');
            $table->foreign('supervisor_id')->references('id')->on('supervisors')->onDelete('set null');
            
            // Indexes
            $table->unique(['name', 'batch_number', 'advisor_id']);
            $table->index(['created_by_type', 'batch_number']);
            $table->index(['advisor_id', 'created_by_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
