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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            // API response fields
            $table->unsignedBigInteger('api_id')->nullable();
            $table->integer('user_info_id')->nullable();
            $table->string('type_id')->nullable();
            $table->string('username')->nullable();
            $table->string('designation')->nullable();
            $table->integer('salt')->nullable();
            $table->unsignedInteger('department_id')->nullable();
            $table->unsignedInteger('program_id')->nullable();
            $table->string('name');
            $table->string('roll')->nullable();
            $table->string('status')->nullable();
            $table->string('department_name')->nullable();
            $table->decimal('cgpa', 4, 2)->nullable();
            $table->decimal('credit', 5, 2)->default(0);
            $table->decimal('total_credit', 5, 2)->default(0);
            $table->timestamp('last_result_update')->nullable();
            $table->string('program_name')->nullable();
            $table->string('batch')->nullable();
            $table->string('profile_image_url')->nullable();
            $table->string('phone')->nullable();
            $table->string('login_type')->default('student');
            $table->text('address')->nullable();
            $table->string('advisor')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
            
            // Add indexes
            $table->index(['username', 'login_type']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
