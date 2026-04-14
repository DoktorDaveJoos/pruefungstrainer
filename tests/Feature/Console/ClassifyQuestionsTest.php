<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\Module;
use App\Models\Question;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('services.anthropic.api_key', 'sk-test');
    config()->set('services.anthropic.model', 'test-model');
});

it('classifies all untagged questions by default', function () {
    Http::fake([
        'api.anthropic.com/*' => Http::sequence()
            ->push(['content' => [['type' => 'text', 'text' => '{"topic":"bausteine","difficulty":"basis"}']]])
            ->push(['content' => [['type' => 'text', 'text' => '{"topic":"methodik","difficulty":"experte"}']]]),
    ]);

    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    $q1 = Question::factory()->for($module)->create();
    $q2 = Question::factory()->for($module)->create();
    $q3 = Question::factory()->for($module)->create([
        'topic' => BsiTopic::Check,
        'difficulty' => QuestionDifficulty::Basis,
    ]);

    $this->artisan('questions:classify')
        ->expectsOutput('Classifying 2 questions…')
        ->assertExitCode(0);

    expect($q1->fresh()->topic)->toBe(BsiTopic::Bausteine);
    expect($q2->fresh()->difficulty)->toBe(QuestionDifficulty::Experte);
    expect($q3->fresh()->topic)->toBe(BsiTopic::Check); // unchanged
});

it('with --force reclassifies already-tagged questions', function () {
    Http::fake([
        'api.anthropic.com/*' => Http::response([
            'content' => [['type' => 'text', 'text' => '{"topic":"standards","difficulty":"experte"}']],
        ]),
    ]);

    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    $question = Question::factory()->for($module)->create([
        'topic' => BsiTopic::Check,
        'difficulty' => QuestionDifficulty::Basis,
    ]);

    $this->artisan('questions:classify', ['--force' => true])
        ->assertExitCode(0);

    expect($question->fresh()->topic)->toBe(BsiTopic::Standards)
        ->and($question->fresh()->difficulty)->toBe(QuestionDifficulty::Experte);
});

it('with --limit processes only N questions', function () {
    Http::fake([
        'api.anthropic.com/*' => Http::response([
            'content' => [['type' => 'text', 'text' => '{"topic":"bausteine","difficulty":"basis"}']],
        ]),
    ]);

    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    Question::factory()->for($module)->count(5)->create();

    $this->artisan('questions:classify', ['--limit' => 2])
        ->assertExitCode(0);

    expect(Question::whereNotNull('topic')->count())->toBe(2);
    expect(Question::whereNull('topic')->count())->toBe(3);
});

it('skips questions when the API returns unparseable output', function () {
    Http::fake([
        'api.anthropic.com/*' => Http::response([
            'content' => [['type' => 'text', 'text' => 'garbage']],
        ]),
    ]);

    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    Question::factory()->for($module)->create();

    $this->artisan('questions:classify')
        ->expectsOutputToContain('Errors: 1')
        ->assertExitCode(0);

    expect(Question::whereNull('topic')->count())->toBe(1);
});

it('fails fast when no API key is configured', function () {
    config()->set('services.anthropic.api_key', null);

    $this->artisan('questions:classify')
        ->expectsOutputToContain('ANTHROPIC_API_KEY is not set')
        ->assertExitCode(1);
});
