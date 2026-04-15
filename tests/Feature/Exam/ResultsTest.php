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
    $this->module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
});

it('renders score + pass status + topic breakdown for a submitted attempt', function () {
    $q1 = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    $q2 = Question::factory()->for($this->module)->tagged(BsiTopic::Bausteine, QuestionDifficulty::Basis)->create();
    Answer::factory()->for($q1)->correct()->create();
    Answer::factory()->for($q2)->correct()->create();

    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'abc-123', 'total_questions' => 2]);
    ExamAnswer::factory()
        ->for($attempt, 'examAttempt')
        ->for($q1)
        ->create([
            'selected_option_ids' => [$q1->answers->first()->id],
            'is_correct' => true,
            'position' => 1,
        ]);
    ExamAnswer::factory()
        ->for($attempt, 'examAttempt')
        ->for($q2)
        ->create(['selected_option_ids' => [], 'is_correct' => false, 'position' => 2]);

    $attempt->update(['score' => 1, 'submitted_at' => now()]);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'abc-123')
        ->get("/pruefungssimulation/{$attempt->id}/ergebnis");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('exam/results')
        ->where('attempt.score', 1)
        ->where('attempt.total_questions', 2)
        ->where('attempt.passed', false)
        ->has('topicBreakdown')
    );
});

it('marks attempt as passed when score >= 60%', function () {
    $q = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    Answer::factory()->for($q)->correct()->create();

    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'abc-123', 'total_questions' => 50, 'score' => 35, 'submitted_at' => now()]);
    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($q)->create(['position' => 1, 'is_correct' => true]);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'abc-123')
        ->get("/pruefungssimulation/{$attempt->id}/ergebnis");

    $response->assertInertia(fn ($page) => $page->where('attempt.passed', true));
});

it('returns 404 for non-owner', function () {
    $attempt = ExamAttempt::factory()->submitted(30)->create(['session_uuid' => 'abc-123']);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'wrong')
        ->get("/pruefungssimulation/{$attempt->id}/ergebnis");

    $response->assertStatus(404);
});

it('redirects to exam page if the attempt is still in progress (not submitted)', function () {
    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'abc-123']);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'abc-123')
        ->get("/pruefungssimulation/{$attempt->id}/ergebnis");

    $response->assertRedirect("/pruefungssimulation/{$attempt->id}");
});
