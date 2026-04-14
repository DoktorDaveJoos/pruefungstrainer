<?php

namespace App\Services;

use App\Models\ExamAnswer;
use App\Models\ExamAttempt;

class ExamScorer
{
    public function score(ExamAttempt $attempt): void
    {
        $attempt->load('examAnswers.question.answers');

        $totalCorrect = 0;

        foreach ($attempt->examAnswers as $examAnswer) {
            $isCorrect = $this->isAnswerCorrect($examAnswer);
            $examAnswer->update(['is_correct' => $isCorrect]);

            if ($isCorrect) {
                $totalCorrect++;
            }
        }

        $attempt->update(['score' => $totalCorrect]);
    }

    /**
     * @return array<string, array{correct: int, total: int}>
     */
    public function topicBreakdown(ExamAttempt $attempt): array
    {
        $attempt->load('examAnswers.question');

        $breakdown = [];

        foreach ($attempt->examAnswers as $examAnswer) {
            $topic = $examAnswer->question->topic?->value;

            if ($topic === null) {
                continue;
            }

            $breakdown[$topic] ??= ['correct' => 0, 'total' => 0];
            $breakdown[$topic]['total']++;

            if ($examAnswer->is_correct) {
                $breakdown[$topic]['correct']++;
            }
        }

        return $breakdown;
    }

    private function isAnswerCorrect(ExamAnswer $examAnswer): bool
    {
        $selected = collect($examAnswer->selected_option_ids ?? []);
        $correctIds = $examAnswer->question->answers->where('is_correct', true)->pluck('id');
        $incorrectIds = $examAnswer->question->answers->where('is_correct', false)->pluck('id');

        $missingCorrect = $correctIds->diff($selected);
        $tickedIncorrect = $selected->intersect($incorrectIds);

        return $missingCorrect->isEmpty() && $tickedIncorrect->isEmpty() && $selected->isNotEmpty();
    }
}
