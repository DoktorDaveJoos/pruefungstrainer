<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\Module;
use App\Models\Question;
use App\Services\QuestionClassifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

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

it('parses a valid JSON response into enum values', function () {
    $classifier = new QuestionClassifier(apiKey: 'test', model: 'test-model');

    $result = $classifier->parseResponse('{"topic":"bausteine","difficulty":"basis"}');

    expect($result)->toBe([
        'topic' => BsiTopic::Bausteine,
        'difficulty' => QuestionDifficulty::Basis,
    ]);
});

it('tolerates surrounding whitespace', function () {
    $classifier = new QuestionClassifier(apiKey: 'test', model: 'test-model');

    $result = $classifier->parseResponse('  {"topic":"methodik","difficulty":"experte"}  ');

    expect($result['topic'])->toBe(BsiTopic::Methodik)
        ->and($result['difficulty'])->toBe(QuestionDifficulty::Experte);
});

it('returns null for invalid JSON', function () {
    $classifier = new QuestionClassifier(apiKey: 'test', model: 'test-model');

    expect($classifier->parseResponse('not json'))->toBeNull()
        ->and($classifier->parseResponse(''))->toBeNull()
        ->and($classifier->parseResponse(null))->toBeNull();
});

it('returns null for unknown enum values', function () {
    $classifier = new QuestionClassifier(apiKey: 'test', model: 'test-model');

    expect($classifier->parseResponse('{"topic":"quantum","difficulty":"basis"}'))->toBeNull()
        ->and($classifier->parseResponse('{"topic":"bausteine","difficulty":"nightmare"}'))->toBeNull();
});

it('returns null when required keys are missing', function () {
    $classifier = new QuestionClassifier(apiKey: 'test', model: 'test-model');

    expect($classifier->parseResponse('{"topic":"bausteine"}'))->toBeNull()
        ->and($classifier->parseResponse('{"difficulty":"basis"}'))->toBeNull()
        ->and($classifier->parseResponse('{}'))->toBeNull();
});

it('posts to the Anthropic Messages API with system prompt + user prompt + cache control', function () {
    Http::fake([
        'api.anthropic.com/*' => Http::response([
            'content' => [['type' => 'text', 'text' => '{"topic":"bausteine","difficulty":"basis"}']],
        ]),
    ]);

    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    $question = Question::factory()->for($module)->create([
        'text' => 'Q?',
        'explanation' => 'E.',
    ]);

    $classifier = new QuestionClassifier(apiKey: 'sk-test', model: 'test-model');
    $result = $classifier->classify($question);

    expect($result['topic'])->toBe(BsiTopic::Bausteine);

    Http::assertSent(function ($request) {
        $body = $request->data();

        return $request->url() === 'https://api.anthropic.com/v1/messages'
            && $request->hasHeader('x-api-key', 'sk-test')
            && $request->hasHeader('anthropic-version', '2023-06-01')
            && $body['model'] === 'test-model'
            && $body['max_tokens'] === 100
            && $body['temperature'] === 0
            && is_array($body['system'])
            && $body['system'][0]['cache_control'] === ['type' => 'ephemeral']
            && str_contains($body['system'][0]['text'], 'bausteine')
            && $body['messages'][0]['role'] === 'user'
            && str_contains($body['messages'][0]['content'], 'Q?');
    });
});

it('returns null when the API call fails', function () {
    Http::fake([
        'api.anthropic.com/*' => Http::response(status: 500),
    ]);

    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    $question = Question::factory()->for($module)->create();

    $classifier = new QuestionClassifier(apiKey: 'sk-test', model: 'test-model');

    expect($classifier->classify($question))->toBeNull();
});

it('returns null when the response text cannot be parsed', function () {
    Http::fake([
        'api.anthropic.com/*' => Http::response([
            'content' => [['type' => 'text', 'text' => 'nope, not json']],
        ]),
    ]);

    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    $question = Question::factory()->for($module)->create();

    $classifier = new QuestionClassifier(apiKey: 'sk-test', model: 'test-model');

    expect($classifier->classify($question))->toBeNull();
});
