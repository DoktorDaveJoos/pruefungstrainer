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
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    $this->question = Question::factory()->for($module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    Answer::factory()->for($this->question)->count(4)->create();
});

it('renders the exam page for the owner of an anonymous attempt', function () {
    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'abc-123']);
    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($this->question)->create(['position' => 1]);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'abc-123')
        ->get("/pruefungssimulation/{$attempt->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('exam/question')
        ->has('attempt')
        ->has('questions', 1)
    );
});

it('returns 404 when the session cookie does not match', function () {
    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'abc-123']);
    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($this->question)->create(['position' => 1]);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'wrong')
        ->get("/pruefungssimulation/{$attempt->id}");

    $response->assertStatus(404);
});

it('redirects to results if the timer has already expired', function () {
    $attempt = ExamAttempt::factory()->expired()->create(['session_uuid' => 'abc-123']);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'abc-123')
        ->get("/pruefungssimulation/{$attempt->id}");

    $response->assertRedirect("/pruefungssimulation/{$attempt->id}/ergebnis");
    expect($attempt->fresh()->submitted_at)->not->toBeNull();
});

it('redirects to results if the attempt is already submitted', function () {
    $attempt = ExamAttempt::factory()->submitted(30)->create(['session_uuid' => 'abc-123']);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'abc-123')
        ->get("/pruefungssimulation/{$attempt->id}");

    $response->assertRedirect("/pruefungssimulation/{$attempt->id}/ergebnis");
});
