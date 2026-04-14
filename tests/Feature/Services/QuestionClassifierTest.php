<?php

use App\Models\Module;
use App\Models\Question;
use App\Services\QuestionClassifier;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('builds a user prompt including question text + explanation + optional quote + source', function () {
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    $question = Question::factory()->for($module)->create([
        'text' => 'Was beschreibt der BSI-Standard 200-1?',
        'explanation' => 'BSI-Standard 200-1 beschreibt die Anforderungen an ein ISMS.',
        'quote' => 'Siehe Kapitel 2.',
        'source' => 'BSI-Standard 200-1, Kapitel 2, S. 8',
    ]);

    $classifier = new QuestionClassifier(apiKey: 'test', model: 'test-model');
    $prompt = $classifier->buildUserPrompt($question);

    expect($prompt)
        ->toContain('Was beschreibt der BSI-Standard 200-1?')
        ->toContain('BSI-Standard 200-1 beschreibt die Anforderungen')
        ->toContain('Zitat: Siehe Kapitel 2.')
        ->toContain('Quelle: BSI-Standard 200-1, Kapitel 2, S. 8');
});

it('omits quote and source sections when they are null', function () {
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    $question = Question::factory()->for($module)->create([
        'text' => 'Minimal question?',
        'explanation' => 'Minimal explanation.',
        'quote' => null,
        'source' => null,
    ]);

    $classifier = new QuestionClassifier(apiKey: 'test', model: 'test-model');
    $prompt = $classifier->buildUserPrompt($question);

    expect($prompt)
        ->toContain('Minimal question?')
        ->toContain('Minimal explanation.')
        ->not->toContain('Zitat:')
        ->not->toContain('Quelle:');
});
