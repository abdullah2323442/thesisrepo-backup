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
            // Track who created the group (admin vs advisor)
            $table->enum('created_by_type', ['admin', 'advisor'])->default('advisor')->after('advisor_id');
            
            // Track the admin user who created the group (if created by admin)
            $table->unsignedBigInteger('created_by_admin_id')->nullable()->after('created_by_type');
            
            // Track if advisor was auto-detected from student API
            $table->boolean('advisor_auto_detected')->default(false)->after('created_by_admin_id');
            
            // Add foreign key for admin creator
            $table->foreign('created_by_admin_id')->references('id')->on('users')->onDelete('set null');
            
            // Add index for better performance
            $table->index(['created_by_type', 'batch_number']);
            $table->index(['advisor_id', 'created_by_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign(['created_by_admin_id']);
            $table->dropIndex(['created_by_type', 'batch_number']);
            $table->dropIndex(['advisor_id', 'created_by_type']);
            $table->dropColumn(['created_by_type', 'created_by_admin_id', 'advisor_auto_detected']);
        });
    }
};