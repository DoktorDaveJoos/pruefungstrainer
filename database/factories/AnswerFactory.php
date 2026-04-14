<?php

namespace Database\Factories;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Answer>
 */
class AnswerFactory extends Factory
{
    protected $model = Answer::class;

    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'text' => fake()->sentence(8),
            'is_correct' => fake()->boolean(25),
        ];
    }

    public function correct(): static
    {
        return $this->state(fn () => ['is_correct' => true]);
    }

    public function incorrect(): static
    {
        return $this->state(fn () => ['is_correct' => false]);
    }
}
