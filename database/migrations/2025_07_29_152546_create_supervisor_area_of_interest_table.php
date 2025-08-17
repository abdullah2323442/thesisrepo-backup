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
        Schema::create('supervisor_area_of_interest', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supervisor_id')->constrained()->onDelete('cascade');
            $table->foreignId('area_of_interest_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['supervisor_id', 'area_of_interest_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supervisor_area_of_interest');
    }
};
