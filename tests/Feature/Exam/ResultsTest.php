<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\Answer;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Module;
use App\Models\Question;
use App\Models\User;
use App\Services\ExamAttemptFinder;
use Danestves\LaravelPolar\Order;

beforeEach(function () {
    $this->module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
});

function createUserWithActiveAccess(): User
{
    $user = User::factory()->create();
    Order::factory()->for($user, 'billable')->create([
        'product_id' => config('polar.products.founder'),
        'ordered_at' => now()->subDays(10),
        'refunded_at' => null,
    ]);

    return $user;
}

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

it('passes reviewItems for wrong answers only when the user has active access', function () {
    $user = createUserWithActiveAccess();

    $wrongQuestion = Question::factory()->for($this->module)->tagged(BsiTopic::Bausteine, QuestionDifficulty::Basis)->create([
        'text' => 'Welche Schutzbedarfskategorien kennt der IT-Grundschutz?',
        'explanation' => 'Drei Kategorien: normal, hoch, sehr hoch.',
        'quote' => 'Die Schutzbedarfskategorien sind normal, hoch und sehr hoch.',
        'source' => 'BSI-Standard 200-2, Kap. 8.2',
    ]);
    $correctAnswer = Answer::factory()->for($wrongQuestion)->correct()->create(['text' => 'Normal, Hoch, Sehr hoch']);
    $wrongAnswer = Answer::factory()->for($wrongQuestion)->incorrect()->create(['text' => 'Normal, Hoch, Kritisch']);

    $rightQuestion = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    $rightAnswer = Answer::factory()->for($rightQuestion)->correct()->create();

    $attempt = ExamAttempt::factory()->for($user)->create(['total_questions' => 2, 'score' => 1, 'submitted_at' => now()]);

    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($rightQuestion)->create([
        'position' => 1,
        'is_correct' => true,
        'selected_option_ids' => [$rightAnswer->id],
        'options_order' => [$rightAnswer->id],
    ]);
    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($wrongQuestion)->create([
        'position' => 2,
        'is_correct' => false,
        'selected_option_ids' => [$wrongAnswer->id],
        'options_order' => [$correctAnswer->id, $wrongAnswer->id],
    ]);

    $response = $this->actingAs($user)->get("/pruefungssimulation/{$attempt->id}/ergebnis");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('exam/results')
        ->where('hasAccess', true)
        ->has('reviewItems', 1)
        ->where('reviewItems.0.number', 2)
        ->where('reviewItems.0.topic', 'Bausteine')
        ->where('reviewItems.0.stem', 'Welche Schutzbedarfskategorien kennt der IT-Grundschutz?')
        ->where('reviewItems.0.explanation', 'Drei Kategorien: normal, hoch, sehr hoch.')
        ->where('reviewItems.0.quote', 'Die Schutzbedarfskategorien sind normal, hoch und sehr hoch.')
        ->where('reviewItems.0.source', 'BSI-Standard 200-2, Kap. 8.2')
        ->has('reviewItems.0.options', 2)
        ->where('reviewItems.0.options.0.text', 'Normal, Hoch, Sehr hoch')
        ->where('reviewItems.0.options.0.isCorrect', true)
        ->where('reviewItems.0.options.0.isUserChoice', false)
        ->where('reviewItems.0.options.1.text', 'Normal, Hoch, Kritisch')
        ->where('reviewItems.0.options.1.isCorrect', false)
        ->where('reviewItems.0.options.1.isUserChoice', true)
    );
});

it('passes reviewItems as null when the user has no active access', function () {
    $attempt = ExamAttempt::factory()->submitted(30)->create(['session_uuid' => 'abc-123']);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, 'abc-123')
        ->get("/pruefungssimulation/{$attempt->id}/ergebnis");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->where('hasAccess', false)
        ->where('reviewItems', null)
    );
});

it('passes an empty reviewItems array for a perfect score', function () {
    $user = createUserWithActiveAccess();

    $q = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    $a = Answer::factory()->for($q)->correct()->create();

    $attempt = ExamAttempt::factory()->for($user)->create(['total_questions' => 1, 'score' => 1, 'submitted_at' => now()]);
    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($q)->create([
        'position' => 1,
        'is_correct' => true,
        'selected_option_ids' => [$a->id],
        'options_order' => [$a->id],
    ]);

    $response = $this->actingAs($user)->get("/pruefungssimulation/{$attempt->id}/ergebnis");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->where('hasAccess', true)
        ->has('reviewItems', 0)
    );
});
