<?php

use App\Models\ExamAttempt;
use App\Models\User;
use App\Services\ExamAttemptFinder;
use Illuminate\Http\Request;

it('matches an attempt owned by the authenticated user', function () {
    $user = User::factory()->create();
    $attempt = ExamAttempt::factory()->forUser($user)->create();

    $request = new Request;
    $request->setUserResolver(fn () => $user);

    $found = (new ExamAttemptFinder)->find($request, $attempt->id);

    expect($found?->id)->toBe($attempt->id);
});

it('returns null when auth user does not own the attempt', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $attempt = ExamAttempt::factory()->forUser($owner)->create();

    $request = new Request;
    $request->setUserResolver(fn () => $intruder);

    $found = (new ExamAttemptFinder)->find($request, $attempt->id);

    expect($found)->toBeNull();
});

it('matches an anonymous attempt by session_uuid cookie', function () {
    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'abc-123']);

    $request = new Request;
    $request->cookies->set('pt_exam_session', 'abc-123');

    $found = (new ExamAttemptFinder)->find($request, $attempt->id);

    expect($found?->id)->toBe($attempt->id);
});

it('returns null when anonymous cookie does not match the attempt', function () {
    $attempt = ExamAttempt::factory()->create(['session_uuid' => 'abc-123']);

    $request = new Request;
    $request->cookies->set('pt_exam_session', 'wrong-uuid');

    $found = (new ExamAttemptFinder)->find($request, $attempt->id);

    expect($found)->toBeNull();
});

it('returns null when the attempt does not exist', function () {
    $user = User::factory()->create();
    $request = new Request;
    $request->setUserResolver(fn () => $user);

    $found = (new ExamAttemptFinder)->find($request, 999999);

    expect($found)->toBeNull();
});
