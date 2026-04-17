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

    $this->sessionUuid = 'abc-123';
    $this->inertia = fn () => $this->withHeaders([
        'X-Inertia' => 'true',
        'X-Requested-With' => 'XMLHttpRequest',
        'Cookie' => ExamAttemptFinder::SESSION_COOKIE.'='.$this->sessionUuid,
    ]);
});

it('saves selected_option_ids and returns a 303 Inertia redirect', function () {
    $attempt = ExamAttempt::factory()->create(['session_uuid' => $this->sessionUuid]);
    $examAnswer = ExamAnswer::factory()->for($attempt, 'examAttempt')->for($this->question)->create(['position' => 1]);
    $optionIds = $this->question->answers->pluck('id')->take(2)->values()->all();

    $response = ($this->inertia)()->patch("/pruefungssimulation/{$attempt->id}/answer/1", [
        'selected_option_ids' => $optionIds,
    ]);

    $response->assertStatus(303);
    expect($examAnswer->fresh()->selected_option_ids)->toBe($optionIds);
});

it('accepts an empty selection', function () {
    $attempt = ExamAttempt::factory()->create(['session_uuid' => $this->sessionUuid]);
    $examAnswer = ExamAnswer::factory()->for($attempt, 'examAttempt')->for($this->question)->create(['position' => 1, 'selected_option_ids' => [1, 2]]);

    $response = ($this->inertia)()->patch("/pruefungssimulation/{$attempt->id}/answer/1", [
        'selected_option_ids' => [],
    ]);

    $response->assertStatus(303);
    expect($examAnswer->fresh()->selected_option_ids)->toBe([]);
});

it('returns 404 when cookie does not match', function () {
    $attempt = ExamAttempt::factory()->create(['session_uuid' => $this->sessionUuid]);
    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($this->question)->create(['position' => 1]);

    $response = $this->withHeaders([
        'X-Inertia' => 'true',
        'X-Requested-With' => 'XMLHttpRequest',
        'Cookie' => ExamAttemptFinder::SESSION_COOKIE.'=wrong',
    ])->patch("/pruefungssimulation/{$attempt->id}/answer/1", [
        'selected_option_ids' => [],
    ]);

    $response->assertStatus(404);
});

it('rejects save after timer has expired and auto-submits', function () {
    $attempt = ExamAttempt::factory()->expired()->create(['session_uuid' => $this->sessionUuid]);
    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($this->question)->create(['position' => 1]);

    $response = ($this->inertia)()->patch("/pruefungssimulation/{$attempt->id}/answer/1", [
        'selected_option_ids' => [],
    ]);

    $response->assertStatus(409);
    expect($attempt->fresh()->submitted_at)->not->toBeNull();
});

it('rejects save when attempt is already submitted', function () {
    $attempt = ExamAttempt::factory()->submitted(30)->create(['session_uuid' => $this->sessionUuid]);
    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($this->question)->create(['position' => 1]);

    $response = ($this->inertia)()->patch("/pruefungssimulation/{$attempt->id}/answer/1", [
        'selected_option_ids' => [],
    ]);

    $response->assertStatus(409);
});

it('returns 404 when position does not exist in the attempt', function () {
    $attempt = ExamAttempt::factory()->create(['session_uuid' => $this->sessionUuid]);
    ExamAnswer::factory()->for($attempt, 'examAttempt')->for($this->question)->create(['position' => 1]);

    $response = ($this->inertia)()->patch("/pruefungssimulation/{$attempt->id}/answer/99", [
        'selected_option_ids' => [],
    ]);

    $response->assertStatus(404);
});
