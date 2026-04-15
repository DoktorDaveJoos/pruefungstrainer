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

it('saves selected_option_ids + flagged for a valid position', function () {
    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'abc-123']);
    $examAnswer = ExamAnswer::factory()->for($attempt, 'examAttempt')->for($this->question)->create(['position' => 1]);
    $optionIds = $this->question->answers->pluck('id')->take(2)->values()->all();

    $response = $this->withHeader('Cookie', ExamAttemptFinder::SESSION_COOKIE.'=abc-123')
        ->patchJson("/pruefungssimulation/{$attempt->id}/answer/1", [
            'selected_option_ids' => $optionIds,
            'flagged' => true,
        ]);

    $response->assertStatus(200);
    expect($examAnswer->fresh()->selected_option_ids)->toBe($optionIds);
    expect($examAnswer->fresh()->flagged)->toBeTrue();
});

it('accepts an empty selection', function () {
    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'abc-123']);
    $examAnswer = ExamAnswer::factory()->for($attempt, 'examAttempt')->for($this->question)->create(['position' => 1, 'selected_option_ids' => [1, 2]]);

    $response = $this->withHeader('Cookie', ExamAttemptFinder::SESSION_COOKIE.'=abc-123')
        ->patchJson("/pruefungssimulation/{$attempt->id}/answer/1", [
            'selected_option_ids' => [],
            'flagged' => false,
        ]);

    $response->assertStatus(200);
    expect($examAnswer->fresh()->selected_option_ids)->toBe([]);
});

it('returns 404 when cookie does not match', function () {
    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'abc-123']);
    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($this->question)->create(['position' => 1]);

    $response = $this->withHeader('Cookie', ExamAttemptFinder::SESSION_COOKIE.'=wrong')
        ->patchJson("/pruefungssimulation/{$attempt->id}/answer/1", [
            'selected_option_ids' => [],
            'flagged' => false,
        ]);

    $response->assertStatus(404);
});

it('rejects save after timer has expired (auto-submit)', function () {
    $attempt = ExamAttempt::factory()->expired()->create(['session_uuid' => 'abc-123']);
    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($this->question)->create(['position' => 1]);

    $response = $this->withHeader('Cookie', ExamAttemptFinder::SESSION_COOKIE.'=abc-123')
        ->patchJson("/pruefungssimulation/{$attempt->id}/answer/1", [
            'selected_option_ids' => [],
            'flagged' => false,
        ]);

    $response->assertStatus(409);
    expect($attempt->fresh()->submitted_at)->not->toBeNull();
});

it('returns 404 when position does not exist in the attempt', function () {
    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'abc-123']);
    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($this->question)->create(['position' => 1]);

    $response = $this->withHeader('Cookie', ExamAttemptFinder::SESSION_COOKIE.'=abc-123')
        ->patchJson("/pruefungssimulation/{$attempt->id}/answer/99", [
            'selected_option_ids' => [],
            'flagged' => false,
        ]);

    $response->assertStatus(404);
});
