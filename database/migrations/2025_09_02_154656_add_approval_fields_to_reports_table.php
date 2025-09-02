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
            $table->string('status')->default('draft')->after('extra_input');
            $table->datetime('approved_at')->nullable()->after('status');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');
            
            // Add foreign key for approved_by
            $table->foreign('approved_by')->references('id')->on('teachers')->onDelete('set null');
            
            // Add indexes for performance
            $table->index('status');
            $table->index('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropIndex(['status']);
            $table->dropIndex(['approved_at']);
            $table->dropColumn(['status', 'approved_at', 'approved_by']);
        });
    }
};