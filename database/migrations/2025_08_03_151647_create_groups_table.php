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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('batch_number');
            $table->unsignedBigInteger('advisor_id');
            $table->integer('max_students')->default(3);
            $table->timestamps();
            
            $table->foreign('advisor_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['name', 'batch_number', 'advisor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
