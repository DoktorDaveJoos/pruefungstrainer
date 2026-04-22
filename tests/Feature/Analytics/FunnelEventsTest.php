<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\Answer;
use App\Models\ExamAttempt;
use App\Models\Module;
use App\Models\Question;
use App\Models\TrackedEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Seed the minimum free-tier questions required for a guest exam draw.
 */
function seedFreeTierQuestions(int $count = 3): void
{
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);

    Question::factory()
        ->for($module)
        ->count($count)
        ->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)
        ->freeTier()
        ->has(Answer::factory()->count(2))
        ->create();
}

it('records the registered event when a new user is created', function (): void {
    $this->post(route('register'), [
        'name' => 'Test User',
        'email' => 'new@example.com',
        'password' => 'SuperSecret123!',
        'password_confirmation' => 'SuperSecret123!',
    ]);

    $event = TrackedEvent::where('name', 'registered')->first();
    expect($event)->not->toBeNull();
    expect($event->user_id)->toBe(User::where('email', 'new@example.com')->value('id'));
});

it('records exam_started when a guest starts an attempt', function (): void {
    seedFreeTierQuestions();

    $this->post(route('exam.start'));

    expect(TrackedEvent::where('name', 'exam_started')->count())->toBe(1);
});

it('records exam_completed with score metadata on submit', function (): void {
    $user = User::factory()->hasActiveAccess()->create();

    $this->actingAs($user)->post(route('exam.start'));
    $attempt = ExamAttempt::latest('id')->first();

    $this->actingAs($user)->post(route('exam.submit', $attempt));

    $event = TrackedEvent::where('name', 'exam_completed')->first();
    expect($event)->not->toBeNull();
    expect($event->metadata)->toHaveKeys(['attempt_id', 'score', 'total']);
    expect($event->metadata['attempt_id'])->toBe($attempt->id);
});
