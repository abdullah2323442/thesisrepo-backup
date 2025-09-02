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
            // Drop the existing foreign key constraint that references non-existent teachers table
            $table->dropForeign(['approved_by']);
            
            // Add new foreign key constraint that references users table
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // Drop the users foreign key
            $table->dropForeign(['approved_by']);
            
            // Restore the original teachers foreign key (even though it won't work)
            $table->foreign('approved_by')->references('id')->on('teachers')->onDelete('set null');
        });
    }
};