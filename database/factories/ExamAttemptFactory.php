<?php

namespace Database\Factories;

use App\Models\ExamAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ExamAttempt>
 */
class ExamAttemptFactory extends Factory
{
    protected $model = ExamAttempt::class;

    public function definition(): array
    {
        $startedAt = now();

        return [
            'user_id' => null,
            'session_uuid' => Str::uuid()->toString(),
            'started_at' => $startedAt,
            'timer_expires_at' => $startedAt->copy()->addMinutes(60),
            'submitted_at' => null,
            'total_questions' => 50,
            'score' => null,
            'is_free_attempt' => true,
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn () => [
            'user_id' => $user->id,
            'session_uuid' => null,
            'is_free_attempt' => false,
        ]);
    }

    public function submitted(int $score): static
    {
        return $this->state(fn () => [
            'submitted_at' => now(),
            'score' => $score,
        ]);
    }

    public function expired(): static
    {
        return $this->state(function () {
            $startedAt = now()->subMinutes(61);

            return [
                'started_at' => $startedAt,
                'timer_expires_at' => $startedAt->copy()->addMinutes(60),
            ];
        });
    }
}
