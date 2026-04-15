<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\Answer;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Module;
use App\Models\Question;
use App\Services\ExamScorer;

function buildQuestion(array $correct, array $wrong, BsiTopic $topic = BsiTopic::Methodik): Question
{
    $module = Module::firstOrCreate(['slug' => 'm2-bsi-grundschutz'], ['name' => 'M2']);
    $q = Question::factory()->for($module)->tagged($topic, QuestionDifficulty::Basis)->create();

    foreach ($correct as $text) {
        Answer::factory()->for($q)->correct()->create(['text' => $text]);
    }
    foreach ($wrong as $text) {
        Answer::factory()->for($q)->incorrect()->create(['text' => $text]);
    }

    $q->load(['answers' => fn ($rel) => $rel->orderBy('text')]);

    return $q;
}

it('scores a question correct only when every correct option is ticked AND no wrong option is ticked', function () {
    $q = buildQuestion(correct: ['A', 'C'], wrong: ['B', 'D']);
    [$a, $b, $c, $d] = $q->answers->pluck('id')->all();

    $attempt = ExamAttempt::factory()->create();
    $examAnswer = ExamAnswer::factory()
        ->for($attempt, 'examAttempt')
        ->for($q)
        ->create(['selected_option_ids' => [$a, $c], 'position' => 1]);

    (new ExamScorer)->score($attempt->fresh());

    expect($examAnswer->fresh()->is_correct)->toBeTrue();
});

it('scores wrong when one correct option is missing', function () {
    $q = buildQuestion(correct: ['A', 'C'], wrong: ['B', 'D']);
    [$a, $b, $c, $d] = $q->answers->pluck('id')->all();

    $attempt = ExamAttempt::factory()->create();
    $examAnswer = ExamAnswer::factory()
        ->for($attempt, 'examAttempt')
        ->for($q)
        ->create(['selected_option_ids' => [$a], 'position' => 1]);

    (new ExamScorer)->score($attempt->fresh());

    expect($examAnswer->fresh()->is_correct)->toBeFalse();
});

it('scores wrong when one wrong option is ticked', function () {
    $q = buildQuestion(correct: ['A'], wrong: ['B']);
    [$a, $b] = $q->answers->pluck('id')->all();

    $attempt = ExamAttempt::factory()->create();
    $examAnswer = ExamAnswer::factory()
        ->for($attempt, 'examAttempt')
        ->for($q)
        ->create(['selected_option_ids' => [$a, $b], 'position' => 1]);

    (new ExamScorer)->score($attempt->fresh());

    expect($examAnswer->fresh()->is_correct)->toBeFalse();
});

it('scores wrong when no options are ticked', function () {
    $q = buildQuestion(correct: ['A'], wrong: ['B']);

    $attempt = ExamAttempt::factory()->create();
    $examAnswer = ExamAnswer::factory()
        ->for($attempt, 'examAttempt')
        ->for($q)
        ->create(['selected_option_ids' => null, 'position' => 1]);

    (new ExamScorer)->score($attempt->fresh());

    expect($examAnswer->fresh()->is_correct)->toBeFalse();
});

it('writes the total score back to the exam_attempt row', function () {
    $q1 = buildQuestion(correct: ['A'], wrong: ['B']);
    $q2 = buildQuestion(correct: ['X'], wrong: ['Y']);
    $q1Correct = $q1->answers->firstWhere('is_correct', true)->id;

    $attempt = ExamAttempt::factory()->create(['total_questions' => 2]);
    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($q1)->create(['selected_option_ids' => [$q1Correct], 'position' => 1]);
    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($q2)->create(['selected_option_ids' => null, 'position' => 2]);

    (new ExamScorer)->score($attempt->fresh());

    expect($attempt->fresh()->score)->toBe(1);
});

it('computes a topic breakdown of correct / total per topic', function () {
    $q1 = buildQuestion(correct: ['A'], wrong: ['B'], topic: BsiTopic::Bausteine);
    $q2 = buildQuestion(correct: ['A'], wrong: ['B'], topic: BsiTopic::Bausteine);
    $q3 = buildQuestion(correct: ['A'], wrong: ['B'], topic: BsiTopic::Methodik);

    $attempt = ExamAttempt::factory()->create(['total_questions' => 3]);
    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($q1)->create(['selected_option_ids' => [$q1->answers->firstWhere('is_correct', true)->id], 'is_correct' => true, 'position' => 1]);
    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($q2)->create(['selected_option_ids' => null, 'is_correct' => false, 'position' => 2]);
    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($q3)->create(['selected_option_ids' => [$q3->answers->firstWhere('is_correct', true)->id], 'is_correct' => true, 'position' => 3]);

    $breakdown = (new ExamScorer)->topicBreakdown($attempt->fresh());

    expect($breakdown[BsiTopic::Bausteine->value])->toBe(['correct' => 1, 'total' => 2]);
    expect($breakdown[BsiTopic::Methodik->value])->toBe(['correct' => 1, 'total' => 1]);
});
