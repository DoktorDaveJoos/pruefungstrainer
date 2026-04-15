<?php

namespace App\Http\Controllers;

use App\Services\PracticeDraw;
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
}
