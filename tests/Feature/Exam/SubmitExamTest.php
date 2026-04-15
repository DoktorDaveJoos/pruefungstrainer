<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\Answer;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Module;
use App\Models\Question;
use App\Services\ExamAttemptFinder;

beforeEach(function () {
    Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
});

it('finalizes score + submitted_at and redirects to results', function () {
    $q = Question::factory()->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    $correct = Answer::factory()->for($q)->correct()->create();
    Answer::factory()->for($q)->incorrect()->create();

    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'abc-123', 'total_questions' => 1]);
    ExamAnswer::factory()
        ->for($attempt, 'examAttempt')
        ->for($q)
        ->create(['selected_option_ids' => [$correct->id], 'position' => 1]);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'abc-123')
        ->post("/pruefungssimulation/{$attempt->id}/submit");

    $response->assertRedirect("/pruefungssimulation/{$attempt->id}/ergebnis");

    $fresh = $attempt->fresh();
    expect($fresh->submitted_at)->not->toBeNull();
    expect($fresh->score)->toBe(1);
});

it('is idempotent — re-submitting a submitted attempt just redirects', function () {
    $attempt = ExamAttempt::factory()->submitted(30)->create(['session_uuid' => 'abc-123']);
    $originalSubmittedAt = $attempt->submitted_at;

    $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'abc-123')
        ->post("/pruefungssimulation/{$attempt->id}/submit");

    expect($attempt->fresh()->submitted_at->toIso8601String())->toBe($originalSubmittedAt->toIso8601String());
});

it('returns 404 for non-owner', function () {
    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'abc-123']);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'wrong')
        ->post("/pruefungssimulation/{$attempt->id}/submit");

    $response->assertStatus(404);
});
