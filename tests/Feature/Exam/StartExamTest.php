<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\ExamAttempt;
use App\Models\Module;
use App\Models\Question;
use App\Models\User;
use App\Services\ExamAttemptFinder;

beforeEach(function () {
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    Question::factory()->for($module)->count(50)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->freeTier()->create();
    Question::factory()->for($module)->count(30)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    Question::factory()->for($module)->count(30)->tagged(BsiTopic::Methodik, QuestionDifficulty::Experte)->create();
});

it('creates an anonymous attempt with session_uuid + 50 exam_answers and redirects', function () {
    $response = $this->post('/pruefungssimulation/start');

    $response->assertStatus(302);

    $attempt = ExamAttempt::latest('id')->first();

    expect($attempt)->not->toBeNull()
        ->and($attempt->user_id)->toBeNull()
        ->and($attempt->session_uuid)->not->toBeNull()
        ->and($attempt->total_questions)->toBe(50)
        ->and($attempt->examAnswers)->toHaveCount(50);

    $response->assertRedirect("/pruefungssimulation/{$attempt->id}");
    $response->assertCookie(ExamAttemptFinder::SESSION_COOKIE, $attempt->session_uuid);
});

it('creates an authenticated attempt with user_id (no session_uuid) when logged in', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/pruefungssimulation/start');

    $attempt = ExamAttempt::latest('id')->first();

    expect($attempt->user_id)->toBe($user->id)
        ->and($attempt->session_uuid)->toBeNull()
        ->and($attempt->is_free_attempt)->toBeFalse()
        ->and($attempt->examAnswers)->toHaveCount(50);

    $response->assertRedirect("/pruefungssimulation/{$attempt->id}");
});

it('assigns unique position 1..50 across exam_answers', function () {
    $this->post('/pruefungssimulation/start');

    $attempt = ExamAttempt::latest('id')->first();
    $positions = $attempt->examAnswers->pluck('position')->sort()->values()->all();

    expect($positions)->toBe(range(1, 50));
});

it('sets timer_expires_at to exactly 60 minutes after started_at', function () {
    $this->post('/pruefungssimulation/start');

    $attempt = ExamAttempt::latest('id')->first();

    $diffMinutes = $attempt->started_at->diffInMinutes($attempt->timer_expires_at);
    expect((int) round($diffMinutes))->toBe(60);
});
