<?php

namespace Database\Factories;

use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamAnswer>
 */
class ExamAnswerFactory extends Factory
{
    protected $model = ExamAnswer::class;

    public function definition(): array
    {
        return [
            'exam_attempt_id' => ExamAttempt::factory(),
            'question_id' => Question::factory(),
            'selected_option_ids' => null,
            'is_correct' => null,
            'flagged' => false,
            'position' => 1,
        ];
    }

    public function flagged(): static
    {
        return $this->state(fn () => ['flagged' => true]);
    }

    public function correct(array $selectedIds): static
    {
        return $this->state(fn () => [
            'selected_option_ids' => $selectedIds,
            'is_correct' => true,
        ]);
    }

    public function incorrect(array $selectedIds): static
    {
        return $this->state(fn () => [
            'selected_option_ids' => $selectedIds,
            'is_correct' => false,
        ]);
    }
}
