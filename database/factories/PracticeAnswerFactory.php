<?php

namespace Database\Factories;

use App\Models\PracticeAnswer;
use App\Models\Question;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PracticeAnswer>
 */
class PracticeAnswerFactory extends Factory
{
    protected $model = PracticeAnswer::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'question_id' => Question::factory(),
            'selected_option_ids' => [],
            'is_correct' => fake()->boolean(),
        ];
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
