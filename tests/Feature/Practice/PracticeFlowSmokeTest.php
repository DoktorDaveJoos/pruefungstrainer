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

    Question::factory()->for($module)->count(5)
        ->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)
        ->has(Answer::factory()->correct()->count(1), 'answers')
        ->has(Answer::factory()->incorrect()->count(2), 'answers')
        ->create();
});

it('walks a paid user through 3 practice questions and tracks progress', function () {
    $user = User::factory()->hasActiveAccess()->create();
    $excludeId = 0;

    for ($i = 0; $i < 3; $i++) {
        $showResponse = $this->actingAs($user)->get("/freies-lernen?exclude={$excludeId}");
        $showResponse->assertStatus(200);

        $questionId = $showResponse->viewData('page')['props']['question']['id'];
        $correctOptionId = Answer::where('question_id', $questionId)->where('is_correct', true)->first()->id;

        $answerResponse = $this->actingAs($user)->postJson('/freies-lernen/answer', [
            'question_id' => $questionId,
            'selected_option_ids' => [$correctOptionId],
        ]);
        $answerResponse->assertStatus(200);
        $answerResponse->assertJson(['is_correct' => true]);

        $excludeId = $questionId;
    }

    $progressResponse = $this->actingAs($user)->get('/freies-lernen');
    $progressResponse->assertInertia(fn ($page) => $page
        ->where('progress.seen', 3)
        ->where('progress.correct', 3)
    );

    expect(PracticeAnswer::where('user_id', $user->id)->count())->toBe(3);
});
