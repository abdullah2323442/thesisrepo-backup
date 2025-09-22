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
        Schema::table('report_annotation_sessions', function (Blueprint $table) {
            $table->string('created_by_type')->default('supervisor')->after('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_annotation_sessions', function (Blueprint $table) {
            $table->dropColumn('created_by_type');
        });
    }
};