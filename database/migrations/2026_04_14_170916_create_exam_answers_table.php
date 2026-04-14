<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->json('selected_option_ids')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->boolean('flagged')->default(false);
            $table->unsignedSmallInteger('position');
            $table->timestamps();

            $table->unique(['exam_attempt_id', 'question_id']);
            $table->index(['exam_attempt_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_answers');
    }
};
