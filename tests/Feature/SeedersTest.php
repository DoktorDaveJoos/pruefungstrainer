<?php

use App\Models\Answer;
use App\Models\Module;
use App\Models\Question;
use Illuminate\Support\Facades\Artisan;

it('seeds the BSI module and all questions', function () {
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\DatabaseSeeder']);

    expect(Module::count())->toBe(1)
        ->and(Module::first()->slug)->toBe('m2-bsi-grundschutz')
        ->and(Question::count())->toBeGreaterThan(100)
        ->and(Question::count())->toBeLessThan(300);
});

it('every question has between 2 and 6 answers', function () {
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\DatabaseSeeder']);

    $counts = Question::withCount('answers')->get()->pluck('answers_count');

    expect($counts->min())->toBeGreaterThanOrEqual(2);
    expect($counts->max())->toBeLessThanOrEqual(6);
});

it('every question has at least one correct answer', function () {
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\DatabaseSeeder']);

    $withoutCorrect = Question::whereDoesntHave('answers', fn ($q) => $q->where('is_correct', true))->count();

    expect($withoutCorrect)->toBe(0);
});

it('every answer belongs to an existing question', function () {
    Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\DatabaseSeeder']);

    $orphaned = Answer::whereNull('question_id')->count();
    expect($orphaned)->toBe(0);
});
