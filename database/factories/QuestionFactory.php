<?php

namespace Database\Factories;

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Enums\SourceDocument;
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
            'external_id' => 'test-'.fake()->unique()->uuid(),
            'module_id' => Module::factory(),
            'text' => fake()->sentence(12).'?',
            'explanation' => fake()->paragraph(),
            'quote' => fake()->sentence(20),
            'learning_objective' => null,
            'source_document' => SourceDocument::Bsi2002,
            'source_chapter' => '4.2',
            'source_chapter_title' => 'Schutzbedarfsfeststellung',
            'source_page_start' => fake()->numberBetween(10, 200),
            'source_page_end' => null,
            'source_baustein_id' => null,
            'source_anforderung_type' => null,
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
