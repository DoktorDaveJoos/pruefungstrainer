<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\Module;
use App\Models\Question;

it('flags exactly 50 questions by default and resets prior flags', function () {
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    Question::factory()
        ->for($module)
        ->count(60)
        ->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)
        ->create();

    // Pre-flag a question that should be reset by the command.
    Question::query()->latest('id')->first()->update(['is_free_tier' => true]);

    $this->artisan('exam:flag-free-tier')
        ->expectsOutput('Flagged 50 question(s) as is_free_tier.')
        ->assertSuccessful();

    expect(Question::where('is_free_tier', true)->count())->toBe(50);
});

it('flags only the specified --ids', function () {
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    $questions = Question::factory()
        ->for($module)
        ->count(5)
        ->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)
        ->create();

    $picked = $questions->take(3)->pluck('id')->implode(',');

    $this->artisan('exam:flag-free-tier', ['--ids' => $picked])->assertSuccessful();

    expect(Question::where('is_free_tier', true)->count())->toBe(3);
});

it('reports failure when explicit --ids do not all match', function () {
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    Question::factory()->for($module)->count(2)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();

    $this->artisan('exam:flag-free-tier', ['--ids' => '1,99999'])->assertFailed();
});

it('distributes the 50 flagged questions across topics per Balance blueprint', function () {
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);

    foreach (BsiTopic::cases() as $topic) {
        Question::factory()
            ->for($module)
            ->count(20)
            ->tagged($topic, QuestionDifficulty::Basis)
            ->create();
    }

    $this->artisan('exam:flag-free-tier')->assertSuccessful();

    // Official BSI Prüfungsbedingungen v1.2 distribution:
    // methodik 32, bausteine 14, standards 14, check 12, risikoanalyse 10,
    // modellierung 8, siem 6, notfall 4 → exact integer split of 50:
    $expected = [
        'methodik' => 16,
        'bausteine' => 7,
        'standards' => 7,
        'check' => 6,
        'risikoanalyse' => 5,
        'modellierung' => 4,
        'siem' => 3,
        'notfall' => 2,
    ];

    foreach ($expected as $topic => $count) {
        expect(Question::query()->where('topic', $topic)->where('is_free_tier', true)->count())
            ->toBe($count, "topic {$topic} expected {$count}");
    }

    expect(Question::where('is_free_tier', true)->count())->toBe(50);
});

it('fills shortfall from other topics when a topic is undersupplied', function () {
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);

    Question::factory()
        ->for($module)
        ->count(60)
        ->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)
        ->create();

    $this->artisan('exam:flag-free-tier')->assertSuccessful();

    expect(Question::where('is_free_tier', true)->count())->toBe(50)
        ->and(Question::where('topic', BsiTopic::Methodik)->where('is_free_tier', true)->count())->toBe(50);
});
