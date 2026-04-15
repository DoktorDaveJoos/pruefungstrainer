<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\Answer;
use App\Models\Module;
use App\Models\PracticeAnswer;
use App\Models\Question;
use App\Models\User;

beforeEach(function () {
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    $this->question = Question::factory()->for($module)
        ->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)
        ->create();
    $this->correct = Answer::factory()->for($this->question)->correct()->create();
    $this->wrong = Answer::factory()->for($this->question)->incorrect()->create();
});

it('records a practice answer and returns is_correct + correct option ids + explanation', function () {
    $user = User::factory()->paid()->create();

    $response = $this->actingAs($user)->postJson('/freies-lernen/answer', [
        'question_id' => $this->question->id,
        'selected_option_ids' => [$this->correct->id],
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'is_correct' => true,
        'correct_option_ids' => [$this->correct->id],
        'explanation' => $this->question->explanation,
    ]);

    expect(PracticeAnswer::where('user_id', $user->id)->count())->toBe(1);
    $row = PracticeAnswer::first();
    expect($row->is_correct)->toBeTrue();
    expect($row->selected_option_ids)->toBe([$this->correct->id]);
});

it('marks wrong when only some correct options are ticked', function () {
    $user = User::factory()->paid()->create();
    $extraCorrect = Answer::factory()->for($this->question)->correct()->create();

    $response = $this->actingAs($user)->postJson('/freies-lernen/answer', [
        'question_id' => $this->question->id,
        'selected_option_ids' => [$this->correct->id],
    ]);

    $response->assertJson(['is_correct' => false]);
    expect(PracticeAnswer::first()->is_correct)->toBeFalse();
});

it('marks wrong when a wrong option is ticked alongside correct', function () {
    $user = User::factory()->paid()->create();

    $response = $this->actingAs($user)->postJson('/freies-lernen/answer', [
        'question_id' => $this->question->id,
        'selected_option_ids' => [$this->correct->id, $this->wrong->id],
    ]);

    $response->assertJson(['is_correct' => false]);
});

it('marks wrong when no options are ticked', function () {
    $user = User::factory()->paid()->create();

    $response = $this->actingAs($user)->postJson('/freies-lernen/answer', [
        'question_id' => $this->question->id,
        'selected_option_ids' => [],
    ]);

    $response->assertJson(['is_correct' => false]);
});

it('returns 403 for an unpaid user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson('/freies-lernen/answer', [
        'question_id' => $this->question->id,
        'selected_option_ids' => [$this->correct->id],
    ])->assertStatus(403);
});

it('returns 422 for non-existent question', function () {
    $user = User::factory()->paid()->create();

    $this->actingAs($user)->postJson('/freies-lernen/answer', [
        'question_id' => 999999,
        'selected_option_ids' => [],
    ])->assertStatus(422);
});
