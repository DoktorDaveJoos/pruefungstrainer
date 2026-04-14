<?php

namespace App\Services;

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
}
