<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add new columns to match API response
            $table->unsignedBigInteger('api_id')->nullable()->after('id');
            $table->unsignedInteger('department_id')->nullable()->after('api_id');
            $table->unsignedInteger('program_id')->nullable()->after('department_id');
            $table->string('roll')->nullable()->after('name');
            $table->string('status')->nullable()->after('roll');
            $table->string('department_name')->nullable()->after('status');
            $table->decimal('cgpa', 4, 2)->nullable()->after('department_name');
            $table->decimal('credit', 5, 2)->default(0)->after('cgpa');
            $table->decimal('total_credit', 5, 2)->default(0)->after('credit');
            $table->timestamp('last_result_update')->nullable()->after('total_credit');
            $table->string('program_name')->nullable()->after('last_result_update');
            $table->string('batch')->nullable()->after('program_name');
            $table->string('profile_image_url')->nullable()->after('batch');
            $table->string('phone')->nullable()->after('profile_image_url');
            $table->string('login_type')->default('student')->after('phone');
            $table->text('address')->nullable()->after('login_type');
            $table->string('advisor')->nullable()->after('address');
            
            // Make email nullable since it might not always be provided
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'api_id', 'department_id', 'program_id', 'roll', 'status',
                'department_name', 'cgpa', 'credit', 'total_credit',
                'last_result_update', 'program_name', 'batch', 'profile_image_url',
                'phone', 'login_type', 'address', 'advisor'
            ]);
        });
    }
};
