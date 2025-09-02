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
        Schema::table('reports', function (Blueprint $table) {
            $table->unsignedBigInteger('area_of_interest_id')->nullable()->after('group_id');
            
            // Add foreign key for area_of_interest_id
            $table->foreign('area_of_interest_id')->references('id')->on('area_of_interests')->onDelete('set null');
            
            // Add index for performance
            $table->index('area_of_interest_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['area_of_interest_id']);
            $table->dropIndex(['area_of_interest_id']);
            $table->dropColumn('area_of_interest_id');
        });
    }
};