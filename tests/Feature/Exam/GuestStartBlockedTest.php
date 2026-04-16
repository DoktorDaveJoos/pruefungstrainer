<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\ExamAttempt;
use App\Models\Module;
use App\Models\Question;
use App\Models\User;
use App\Services\ExamAttemptFinder;
use Illuminate\Support\Str;

beforeEach(function () {
    $module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
    Question::factory()
        ->for($module)
        ->count(50)
        ->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)
        ->freeTier()
        ->create();
});

it('redirects guests with a submitted-attempt cookie back to the landing page instead of starting a new attempt', function () {
    $uuid = Str::uuid()->toString();
    ExamAttempt::factory()->submitted(30)->create([
        'user_id' => null,
        'session_uuid' => $uuid,
    ]);

    $countBefore = ExamAttempt::count();

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, $uuid)
        ->post('/pruefungssimulation/start');

    $response->assertRedirect('/');
    expect(ExamAttempt::count())->toBe($countBefore);
});

it('resumes an in-progress guest attempt when the cookie matches', function () {
    $uuid = Str::uuid()->toString();
    $attempt = ExamAttempt::factory()->create([
        'user_id' => null,
        'session_uuid' => $uuid,
        'submitted_at' => null,
    ]);

    $countBefore = ExamAttempt::count();

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, $uuid)
        ->post('/pruefungssimulation/start');

    $response->assertRedirect("/pruefungssimulation/{$attempt->id}");
    expect(ExamAttempt::count())->toBe($countBefore);
});

it('lets authenticated users start regardless of any guest cookie state', function () {
    $user = User::factory()->create();
    $uuid = Str::uuid()->toString();
    ExamAttempt::factory()->submitted(30)->create([
        'user_id' => null,
        'session_uuid' => $uuid,
    ]);

    $response = $this->actingAs($user)
        ->withCookie(ExamAttemptFinder::SESSION_COOKIE, $uuid)
        ->post('/pruefungssimulation/start');

    $response->assertStatus(302);
    $latest = ExamAttempt::latest('id')->first();
    expect($latest->user_id)->toBe($user->id);
});

it('extends the guest cookie ttl to roughly 1 year when the attempt is submitted', function () {
    $uuid = Str::uuid()->toString();
    $attempt = ExamAttempt::factory()->create([
        'user_id' => null,
        'session_uuid' => $uuid,
        'submitted_at' => null,
    ]);

    // Answer one question so the scorer has data.
    $attempt->examAnswers()->create([
        'question_id' => Question::first()->id,
        'position' => 1,
        'options_order' => [],
    ]);

    $response = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, $uuid)
        ->post("/pruefungssimulation/{$attempt->id}/submit");

    $cookie = $response->getCookie(ExamAttemptFinder::SESSION_COOKIE);
    expect($cookie)->not->toBeNull()
        ->and($cookie->getValue())->toBe($uuid);

    $expiresIn = $cookie->getExpiresTime() - time();
    // Allow a generous lower bound: ~1 year minus a day of slack.
    expect($expiresIn)->toBeGreaterThan(60 * 60 * 24 * 360);
});
