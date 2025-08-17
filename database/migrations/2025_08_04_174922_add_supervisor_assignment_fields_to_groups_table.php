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
            $table->unsignedBigInteger('supervisor_id')->nullable()->after('area_of_interest_id');
            $table->boolean('is_manual_assignment')->default(false)->after('supervisor_id');
            $table->integer('assignment_priority')->nullable()->after('is_manual_assignment');
            $table->timestamp('assigned_at')->nullable()->after('assignment_priority');
            
            $table->foreign('supervisor_id')->references('id')->on('supervisors')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign(['supervisor_id']);
            $table->dropColumn(['supervisor_id', 'is_manual_assignment', 'assignment_priority', 'assigned_at']);
        });
    }
};
