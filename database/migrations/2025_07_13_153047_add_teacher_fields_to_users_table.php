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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('user_info_id')->nullable()->after('api_id');
            $table->string('type_id')->nullable()->after('user_info_id');
            $table->string('username')->nullable()->after('type_id');
            $table->string('designation')->nullable()->after('username');
            $table->integer('salt')->nullable()->after('designation');
            
            // Add index for username for teacher lookups
            $table->index(['username', 'login_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['username', 'login_type']);
            $table->dropColumn([
                'user_info_id',
                'type_id', 
                'username',
                'designation',
                'salt'
            ]);
        });
    }
};
