<?php

use App\Models\ExamAttempt;
use App\Models\User;

it('deletes guest attempts older than the cutoff and keeps fresh ones', function () {
    $stale = ExamAttempt::factory()->create([
        'user_id' => null,
        'created_at' => now()->subDays(100),
    ]);

    $fresh = ExamAttempt::factory()->create([
        'user_id' => null,
        'created_at' => now()->subDays(10),
    ]);

    $this->artisan('exam:purge-orphan-attempts')->assertSuccessful();

    expect(ExamAttempt::find($stale->id))->toBeNull()
        ->and(ExamAttempt::find($fresh->id))->not->toBeNull();
});

it('never deletes attempts that already belong to a user', function () {
    $user = User::factory()->create();
    $oldClaimed = ExamAttempt::factory()->forUser($user)->create([
        'created_at' => now()->subDays(500),
    ]);

    $this->artisan('exam:purge-orphan-attempts')->assertSuccessful();

    expect(ExamAttempt::find($oldClaimed->id))->not->toBeNull();
});

it('respects the --days option', function () {
    $thirtyDaysOld = ExamAttempt::factory()->create([
        'user_id' => null,
        'created_at' => now()->subDays(30),
    ]);

    $this->artisan('exam:purge-orphan-attempts', ['--days' => 7])->assertSuccessful();

    expect(ExamAttempt::find($thirtyDaysOld->id))->toBeNull();
});
