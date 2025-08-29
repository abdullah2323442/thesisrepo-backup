<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('group_student_id')->constrained()->cascadeOnDelete();
            $table->boolean('present')->default(false);
            $table->timestamps();

            $table->unique(['meeting_id', 'group_student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_attendances');
    }
};
