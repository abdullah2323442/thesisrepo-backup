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
        Schema::create('report_annotation_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->onDelete('cascade');
            $table->foreignId('submission_id')->references('id')->on('student_report_submissions')->onDelete('cascade');
            $table->foreignId('supervisor_id')->references('id')->on('users');
            $table->integer('version')->default(1);
            $table->text('message')->nullable();
            $table->longText('annotations_json');
            $table->string('annotated_file_path')->nullable();
            $table->boolean('is_sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            $table->index(['report_id', 'submission_id']);
            $table->index(['supervisor_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_annotation_sessions');
    }
};