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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('area_of_interest_id')->nullable();
            $table->enum('type', ['general', 'final']);
            $table->string('project_title')->nullable();
            $table->longText('abstract_md')->nullable();
            $table->text('extra_input')->nullable();
            $table->text('supervisor_message')->nullable();
            $table->string('status')->default('draft');
            $table->datetime('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->text('keywords')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign key constraints
            $table->foreign('area_of_interest_id')->references('id')->on('area_of_interests')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes for performance
            $table->index(['group_id', 'created_at']);
            $table->index('type');
            $table->index('area_of_interest_id');
            $table->index('status');
            $table->index('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};