<?php

use App\Enums\AnforderungType;
use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Enums\SourceDocument;
use App\Models\Module;
use App\Models\Question;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('persists a question with all structured source fields', function () {
    $module = Module::factory()->create();

    $question = Question::factory()->for($module)->create([
        'external_id' => 'bsi-200-2-04.2-001',
        'source_document' => SourceDocument::Bsi2002,
        'source_chapter' => '4.2',
        'source_chapter_title' => 'Schutzbedarfsfeststellung',
        'source_page_start' => 42,
        'source_page_end' => 51,
        'learning_objective' => 'Unterscheidung Schutzbedarfskategorien',
        'topic' => BsiTopic::Methodik,
        'difficulty' => QuestionDifficulty::Basis,
    ]);

    $question->refresh();

    expect($question->external_id)->toBe('bsi-200-2-04.2-001')
        ->and($question->source_document)->toBe(SourceDocument::Bsi2002)
        ->and($question->source_chapter)->toBe('4.2')
        ->and($question->source_page_start)->toBe(42)
        ->and($question->source_page_end)->toBe(51)
        ->and($question->topic)->toBe(BsiTopic::Methodik)
        ->and($question->difficulty)->toBe(QuestionDifficulty::Basis);
});

it('formats source_citation for standards as "BSI-Standard X, Kapitel Y, S. Z"', function () {
    $question = Question::factory()->create([
        'source_document' => SourceDocument::Bsi2001,
        'source_chapter' => '2.1',
        'source_page_start' => 8,
        'source_baustein_id' => null,
    ]);

    expect($question->source_citation)->toBe('BSI-Standard 200-1, Kapitel 2.1, S. 8');
});

it('formats source_citation for Kompendium questions using the Baustein-ID', function () {
    $question = Question::factory()->create([
        'source_document' => SourceDocument::Kompendium,
        'source_chapter' => 'SYS.1.1',
        'source_chapter_title' => 'Allgemeiner Server',
        'source_page_start' => 1,
        'source_baustein_id' => 'SYS.1.1.A4',
        'source_anforderung_type' => AnforderungType::Basis,
    ]);

    expect($question->source_citation)->toBe('IT-Grundschutz-Kompendium, SYS.1.1.A4')
        ->and($question->source_anforderung_type)->toBe(AnforderungType::Basis);
});

it('enforces unique external_id', function () {
    Question::factory()->create(['external_id' => 'dup-1']);

    expect(fn () => Question::factory()->create(['external_id' => 'dup-1']))
        ->toThrow(UniqueConstraintViolationException::class);
});

it('requires a quote (NOT NULL)', function () {
    expect(fn () => Question::factory()->create(['quote' => null]))
        ->toThrow(QueryException::class);
});
