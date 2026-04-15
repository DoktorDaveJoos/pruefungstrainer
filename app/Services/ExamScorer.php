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
     * @return list<array{key: string, label: string, correct: int, total: int}>
     */
    public function topicBreakdown(ExamAttempt $attempt): array
    {
        $attempt->load('examAnswers.question');

        $tally = [];

        foreach ($attempt->examAnswers as $examAnswer) {
            $topic = $examAnswer->question->topic;

            if ($topic === null) {
                continue;
            }

            $tally[$topic->value] ??= ['key' => $topic->value, 'label' => $topic->label(), 'correct' => 0, 'total' => 0];
            $tally[$topic->value]['total']++;

            if ($examAnswer->is_correct) {
                $tally[$topic->value]['correct']++;
            }
        }

        return array_values($tally);
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
