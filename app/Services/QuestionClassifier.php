<?php

namespace App\Services;

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\Question;
use Illuminate\Support\Facades\Http;

class QuestionClassifier
{
    private const SYSTEM_PROMPT = <<<'TEXT'
You classify German BSI IT-Grundschutz-Praktiker exam questions by curriculum topic and difficulty.

Topics (choose exactly one):
- methodik: IT-Grundschutz methodology, process phases, planning
- bausteine: specific Bausteine (SYS.x, APP.x, NET.x, INF.x, OPS.x, ORP.x, CON.x, IND.x) and their application
- risikoanalyse: risk analysis methodology, BSI-Standard 200-3
- modellierung: modeling, scope definition, asset mapping, Informationsverbund
- check: IT-Grundschutz-Check, audit mechanics, Grundschutz-Test-Kriterien
- standards: BSI-Standards 100-x / 200-x themselves, certification framework, legal/compliance context
- notfall: Notfallmanagement, BCM, recovery strategies, BSI-Standard 200-4
- siem: SIEM, monitoring, logging, SOC patterns

Difficulty (choose exactly one):
- basis: fundamental concepts, definitions, basic methodology
- experte: edge cases, cross-cutting scenarios, advanced application, detailed analysis

Respond with valid JSON only: {"topic":"<value>","difficulty":"<value>"}
No explanation, no preamble, no markdown fences.
TEXT;

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

        if ($question->source_document !== null) {
            $parts[] = '';
            $parts[] = 'Quelle: '.$question->source_citation;
        }

        return implode("\n", $parts);
    }

    /**
     * @return array{topic: BsiTopic, difficulty: QuestionDifficulty}|null
     */
    public function classify(Question $question): ?array
    {
        $response = Http::withHeaders([
            'x-api-key' => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post($this->baseUrl.'/messages', [
            'model' => $this->model,
            'max_tokens' => 100,
            'temperature' => 0,
            'system' => [
                [
                    'type' => 'text',
                    'text' => self::SYSTEM_PROMPT,
                    'cache_control' => ['type' => 'ephemeral'],
                ],
            ],
            'messages' => [
                ['role' => 'user', 'content' => $this->buildUserPrompt($question)],
            ],
        ]);

        if (! $response->successful()) {
            return null;
        }

        return $this->parseResponse($response->json('content.0.text'));
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
