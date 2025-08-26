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
        Schema::table('groups', function (Blueprint $table) {
            // Add column to store which area of interest was actually matched during supervisor assignment
            $table->unsignedBigInteger('matched_area_of_interest_id')->nullable()->after('area_of_interest_id');
            
            // Add foreign key constraint
            $table->foreign('matched_area_of_interest_id')
                  ->references('id')
                  ->on('area_of_interests')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign(['matched_area_of_interest_id']);
            $table->dropColumn('matched_area_of_interest_id');
        });
    }
};