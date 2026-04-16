<?php

namespace Database\Factories;

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\Module;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'module_id' => Module::factory(),
            'text' => fake()->sentence(12).'?',
            'explanation' => fake()->paragraph(),
            'quote' => fake()->optional()->sentence(20),
            'source' => fake()->optional()->randomElement([
                'BSI-Standard 200-1, Kapitel 2, S. 8',
                'BSI-Standard 200-2, Kapitel 4, S. 17',
                'IT-Grundschutz-Kompendium, SYS.1.1',
            ]),
            'topic' => null,
            'difficulty' => null,
        ];
    }

    public function tagged(BsiTopic $topic, QuestionDifficulty $difficulty): static
    {
        return $this->state(fn () => [
            'topic' => $topic,
            'difficulty' => $difficulty,
        ]);
    }

    public function freeTier(): static
    {
        return $this->state(fn () => ['is_free_tier' => true]);
    }
}
