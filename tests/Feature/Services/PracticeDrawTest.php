<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\Module;
use App\Models\PracticeAnswer;
use App\Models\Question;
use App\Models\User;
use App\Services\PracticeDraw;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
});

it('returns null when no tagged questions exist', function () {
    expect((new PracticeDraw)->next($this->user->id))->toBeNull();
});

it('returns a tagged question for an empty-history user', function () {
    Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();

    $q = (new PracticeDraw)->next($this->user->id);

    expect($q)->not->toBeNull()
        ->and($q->topic)->toBe(BsiTopic::Methodik);
});

it('skips untagged questions', function () {
    Question::factory()->for($this->module)->create();

    expect((new PracticeDraw)->next($this->user->id))->toBeNull();
});

it('prefers an unseen question over a seen one', function () {
    $seenQuestion = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    $unseenQuestion = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();

    PracticeAnswer::factory()->for($this->user)->for($seenQuestion)->create();

    $q = (new PracticeDraw)->next($this->user->id);

    expect($q->id)->toBe($unseenQuestion->id);
});

it('falls back to seen questions when all are seen', function () {
    $a = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    PracticeAnswer::factory()->for($this->user)->for($a)->create();

    $q = (new PracticeDraw)->next($this->user->id);

    expect($q->id)->toBe($a->id);
});

it('respects exclude parameter', function () {
    $a = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    $b = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();

    $q = (new PracticeDraw)->next($this->user->id, excludeQuestionId: $a->id);

    expect($q->id)->toBe($b->id);
});

it('wrong-only mode returns a question whose latest answer was wrong', function () {
    $wrongQ = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    $rightQ = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();

    PracticeAnswer::factory()->for($this->user)->for($wrongQ)->incorrect([1])->create();
    PracticeAnswer::factory()->for($this->user)->for($rightQ)->correct([1])->create();

    $q = (new PracticeDraw)->next($this->user->id, wrongOnly: true);

    expect($q->id)->toBe($wrongQ->id);
});

it('wrong-only mode uses MOST RECENT answer (a later-correct beats earlier-wrong)', function () {
    $q = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();

    PracticeAnswer::factory()->for($this->user)->for($q)->incorrect([1])->create();
    PracticeAnswer::factory()->for($this->user)->for($q)->correct([1])->create();

    $next = (new PracticeDraw)->next($this->user->id, wrongOnly: true);

    expect($next)->toBeNull();
});

it('wrong-only mode returns null when no wrong-and-not-yet-corrected questions exist', function () {
    $q = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    PracticeAnswer::factory()->for($this->user)->for($q)->correct([1])->create();

    expect((new PracticeDraw)->next($this->user->id, wrongOnly: true))->toBeNull();
});

it('progressFor reports seen / total / correct counts', function () {
    $a = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    $b = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    $c = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();

    PracticeAnswer::factory()->for($this->user)->for($a)->correct([1])->create();
    PracticeAnswer::factory()->for($this->user)->for($b)->incorrect([1])->create();

    $progress = (new PracticeDraw)->progressFor($this->user->id);

    expect($progress)->toBe([
        'seen' => 2,
        'total' => 3,
        'correct' => 1,
    ]);
});

it('progressFor counts MOST RECENT answer per question', function () {
    $q = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();

    PracticeAnswer::factory()->for($this->user)->for($q)->incorrect([1])->create();
    PracticeAnswer::factory()->for($this->user)->for($q)->correct([1])->create();

    $progress = (new PracticeDraw)->progressFor($this->user->id);

    expect($progress)->toBe([
        'seen' => 1,
        'total' => 1,
        'correct' => 1,
    ]);
});
