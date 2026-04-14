<?php

namespace App\Services;

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\Question;

class QuestionClassifier
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
        private readonly string $baseUrl = 'https://api.anthropic.com/v1',
    ) {}

    public function buildUserPrompt(Question $question): string
    {
        $parts = [
            'Frage: '.$question->text,
            '',
            'Erklärung: '.$question->explanation,
        ];

        if ($question->quote !== null) {
            $parts[] = '';
            $parts[] = 'Zitat: '.$question->quote;
        }

        if ($question->source !== null) {
            $parts[] = '';
            $parts[] = 'Quelle: '.$question->source;
        }

        return implode("\n", $parts);
    }

    /**
     * @return array{topic: BsiTopic, difficulty: QuestionDifficulty}|null
     */
    public function parseResponse(?string $text): ?array
    {
        if ($text === null || trim($text) === '') {
            return null;
        }

        $decoded = json_decode(trim($text), true);

        if (! is_array($decoded)) {
            return null;
        }

        if (! isset($decoded['topic']) || ! isset($decoded['difficulty'])) {
            return null;
        }

        $topic = BsiTopic::tryFrom($decoded['topic']);
        $difficulty = QuestionDifficulty::tryFrom($decoded['difficulty']);

        if ($topic === null || $difficulty === null) {
            return null;
        }

        return ['topic' => $topic, 'difficulty' => $difficulty];
    }
}
