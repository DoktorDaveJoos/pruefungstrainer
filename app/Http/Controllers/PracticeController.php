<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavePracticeAnswerRequest;
use App\Models\PracticeAnswer;
use App\Models\Question;
use App\Services\PracticeDraw;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class PracticeController extends Controller
{
    public function __construct(private readonly PracticeDraw $practiceDraw) {}

    public function show(Request $request): Response
    {
        $user = $request->user();
        $wrongOnly = $request->boolean('wrong_only');
        $excludeId = $request->integer('exclude') ?: null;

        $question = $this->practiceDraw->next($user->id, $wrongOnly, $excludeId);

        return inertia('freies-lernen/index', [
            'question' => $question === null ? null : [
                'id' => $question->id,
                'text' => $question->text,
                'topic' => $question->topic?->value,
                'topic_label' => $question->topic?->label(),
                'options' => $question->answers()->get(['id', 'text'])->shuffle()->values(),
            ],
            'wrongOnly' => $wrongOnly,
            'progress' => $this->practiceDraw->progressFor($user->id),
        ]);
    }

    public function saveAnswer(SavePracticeAnswerRequest $request): JsonResponse
    {
        $user = $request->user();
        $question = Question::with('answers')->findOrFail($request->integer('question_id'));
        $selected = collect($request->input('selected_option_ids', []));

        $correctIds = $question->answers->where('is_correct', true)->pluck('id');
        $incorrectIds = $question->answers->where('is_correct', false)->pluck('id');

        $missingCorrect = $correctIds->diff($selected);
        $tickedIncorrect = $selected->intersect($incorrectIds);
        $isCorrect = $missingCorrect->isEmpty() && $tickedIncorrect->isEmpty() && $selected->isNotEmpty();

        PracticeAnswer::create([
            'user_id' => $user->id,
            'question_id' => $question->id,
            'selected_option_ids' => $selected->values()->all(),
            'is_correct' => $isCorrect,
        ]);

        return response()->json([
            'is_correct' => $isCorrect,
            'correct_option_ids' => $correctIds->values()->all(),
            'explanation' => $question->explanation,
            'quote' => $question->quote,
            'source' => $question->source,
        ]);
    }
}
