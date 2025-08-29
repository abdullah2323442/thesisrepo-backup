<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->date('meeting_date');
            $table->text('discussed_topics')->nullable();
            $table->text('outcomes')->nullable();
            $table->timestamps();

            $table->index(['group_id', 'meeting_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
