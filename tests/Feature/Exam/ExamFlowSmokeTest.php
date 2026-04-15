<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\Answer;
use App\Models\ExamAttempt;
use App\Models\Module;
use App\Models\Question;
use App\Services\ExamAttemptFinder;

beforeEach(function () {
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);

    Question::factory()
        ->for($module)
        ->count(60)
        ->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)
        ->has(Answer::factory()->count(2)->correct(), 'answers')
        ->has(Answer::factory()->count(2)->incorrect(), 'answers')
        ->create();

    Question::factory()
        ->for($module)
        ->count(20)
        ->tagged(BsiTopic::Bausteine, QuestionDifficulty::Experte)
        ->has(Answer::factory()->count(1)->correct(), 'answers')
        ->has(Answer::factory()->count(3)->incorrect(), 'answers')
        ->create();
});

it('walks an anonymous user through start → answer 50 → submit → results', function () {
    // Start
    $startResponse = $this->post('/pruefungssimulation/start');
    $startResponse->assertStatus(302);
    $sessionUuid = $startResponse->getCookie(ExamAttemptFinder::SESSION_COOKIE)->getValue();

    $attemptUrl = $startResponse->headers->get('Location');
    preg_match('#/pruefungssimulation/(\d+)$#', $attemptUrl, $matches);
    $attemptId = (int) $matches[1];

    // Show the exam
    $showResponse = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, $sessionUuid)->get($attemptUrl);
    $showResponse->assertStatus(200);

    // Answer all 50 questions by ticking the first option (likely wrong, but valid input)
    $attempt = ExamAttempt::find($attemptId);
    foreach ($attempt->examAnswers as $examAnswer) {
        $firstOptionId = Answer::where('question_id', $examAnswer->question_id)->first()->id;

        $this->withHeader('Cookie', ExamAttemptFinder::SESSION_COOKIE.'='.$sessionUuid)
            ->withHeaders(['X-Inertia' => 'true', 'X-Requested-With' => 'XMLHttpRequest'])
            ->patch("/pruefungssimulation/{$attemptId}/answer/{$examAnswer->position}", [
                'selected_option_ids' => [$firstOptionId],
                'flagged' => false,
            ])
            ->assertStatus(303);
    }

    // Submit
    $this->flushHeaders();
    $submitResponse = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, $sessionUuid)
        ->post("/pruefungssimulation/{$attemptId}/submit");
    $submitResponse->assertRedirect("/pruefungssimulation/{$attemptId}/ergebnis");

    // View results
    $this->flushHeaders();
    $resultsResponse = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, $sessionUuid)
        ->get("/pruefungssimulation/{$attemptId}/ergebnis");
    $resultsResponse->assertStatus(200);
    $resultsResponse->assertInertia(fn ($page) => $page
        ->component('exam/results')
        ->has('attempt.score')
        ->has('topicBreakdown')
    );

    // Attempt is submitted, score populated
    $fresh = $attempt->fresh();
    expect($fresh->submitted_at)->not->toBeNull();
    expect($fresh->score)->toBeInt();
});

it('preserves answer option order across repeated show requests', function () {
    $startResponse = $this->post('/pruefungssimulation/start');
    $sessionUuid = $startResponse->getCookie(ExamAttemptFinder::SESSION_COOKIE)->getValue();
    $attemptUrl = $startResponse->headers->get('Location');

    $extractOrder = fn ($response) => collect($response->viewData('page')['props']['questions'])
        ->map(fn ($q) => collect($q['options'])->pluck('id')->all())
        ->all();

    $first = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, $sessionUuid)->get($attemptUrl);
    $second = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, $sessionUuid)->get($attemptUrl);

    expect($extractOrder($first))->toBe($extractOrder($second));
});
