<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('session_uuid')->nullable()->unique();
            $table->timestamp('started_at');
            $table->timestamp('timer_expires_at');
            $table->timestamp('submitted_at')->nullable();
            $table->unsignedSmallInteger('total_questions')->default(50);
            $table->unsignedSmallInteger('score')->nullable();
            $table->boolean('is_free_attempt')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};
