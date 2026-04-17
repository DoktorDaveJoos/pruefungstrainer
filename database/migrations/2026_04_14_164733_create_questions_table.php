<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->text('text');
            $table->text('explanation');
            $table->text('quote');
            $table->text('learning_objective')->nullable();

            $table->string('source_document')->index();
            $table->string('source_chapter');
            $table->string('source_chapter_title');
            $table->unsignedSmallInteger('source_page_start');
            $table->unsignedSmallInteger('source_page_end')->nullable();
            $table->string('source_baustein_id')->nullable()->index();
            $table->string('source_anforderung_type')->nullable();

            $table->string('topic')->nullable()->index();
            $table->string('difficulty')->nullable()->index();
            $table->boolean('is_free_tier')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
