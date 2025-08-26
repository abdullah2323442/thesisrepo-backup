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
        // Create pivot table for many-to-many relationship between groups and areas of interest
        Schema::create('group_area_of_interest', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->onDelete('cascade');
            $table->foreignId('area_of_interest_id')->constrained('area_of_interests')->onDelete('cascade');
            $table->timestamps();
            
            // Ensure unique combination of group and area of interest
            $table->unique(['group_id', 'area_of_interest_id']);
            
            // Add indexes for better query performance
            $table->index('group_id');
            $table->index('area_of_interest_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_area_of_interest');
    }
};