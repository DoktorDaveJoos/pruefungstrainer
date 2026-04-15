<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveAnswerRequest;
use App\Models\ExamAttempt;
use App\Services\ExamAttemptFinder;
use App\Services\ExamDraw;
use App\Services\ExamScorer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Response;

class ExamController extends Controller
{
    public function __construct(
        private readonly ExamDraw $examDraw,
        private readonly ExamAttemptFinder $finder,
    ) {}

    public function start(Request $request): RedirectResponse
    {
        $user = $request->user();
        $sessionUuid = $user ? null : (string) Str::uuid();

        $questions = $this->examDraw->draw(userId: $user?->id, total: 50);

        $attempt = DB::transaction(function () use ($user, $sessionUuid, $questions) {
            $startedAt = now();

            $attempt = ExamAttempt::create([
                'user_id' => $user?->id,
                'session_uuid' => $sessionUuid,
                'started_at' => $startedAt,
                'timer_expires_at' => $startedAt->copy()->addMinutes(60),
                'total_questions' => $questions->count(),
                'is_free_attempt' => $user === null,
            ]);

            foreach ($questions as $i => $question) {
                $attempt->examAnswers()->create([
                    'question_id' => $question->id,
                    'position' => $i + 1,
                    'options_order' => $question->answers->pluck('id')->shuffle()->values()->all(),
                ]);
            }

            return $attempt;
        });

        $response = redirect("/pruefungssimulation/{$attempt->id}");

        if ($sessionUuid !== null) {
            $response->withCookie(Cookie::make(
                ExamAttemptFinder::SESSION_COOKIE,
                $sessionUuid,
                minutes: 60 * 24,
            ));
        }

        return $response;
    }

    public function show(Request $request, int $attempt): Response|RedirectResponse
    {
        $examAttempt = $this->finder->find($request, $attempt);

        if ($examAttempt === null) {
            abort(404);
        }

        if ($examAttempt->isSubmitted() || $examAttempt->hasExpired()) {
            $this->autoSubmitIfNeeded($examAttempt);

            return redirect("/pruefungssimulation/{$examAttempt->id}/ergebnis");
        }

        $examAttempt->load(['examAnswers.question.answers']);

        $questions = $examAttempt->examAnswers
            ->sortBy('position')
            ->values()
            ->map(function ($ea) {
                $answersById = $ea->question->answers->keyBy('id');

                return [
                    'position' => $ea->position,
                    'question_id' => $ea->question_id,
                    'text' => $ea->question->text,
                    'options' => collect($ea->options_order)
                        ->map(fn ($id) => ['id' => $id, 'text' => $answersById[$id]->text])
                        ->all(),
                    'selected_option_ids' => $ea->selected_option_ids ?? [],
                    'flagged' => $ea->flagged,
                ];
            });

        return inertia('exam/question', [
            'attempt' => [
                'id' => $examAttempt->id,
                'timer_expires_at' => $examAttempt->timer_expires_at->toIso8601String(),
                'total_questions' => $examAttempt->total_questions,
            ],
            'questions' => $questions,
        ]);
    }

    public function saveAnswer(SaveAnswerRequest $request, int $attempt, int $position): RedirectResponse
    {
        $examAttempt = $this->finder->find($request, $attempt);

        if ($examAttempt === null) {
            abort(404);
        }

        if ($examAttempt->isSubmitted()) {
            abort(409, 'Attempt already submitted');
        }

        if ($examAttempt->hasExpired()) {
            $this->autoSubmitIfNeeded($examAttempt);
            abort(409, 'Timer expired');
        }

        $examAnswer = $examAttempt->examAnswers()->where('position', $position)->first();

        if ($examAnswer === null) {
            abort(404);
        }

        $examAnswer->update([
            'selected_option_ids' => $request->input('selected_option_ids'),
            'flagged' => $request->boolean('flagged'),
        ]);

        return back();
    }

    public function submit(Request $request, int $attempt): RedirectResponse
    {
        $examAttempt = $this->finder->find($request, $attempt);

        if ($examAttempt === null) {
            abort(404);
        }

        $this->autoSubmitIfNeeded($examAttempt);

        return redirect("/pruefungssimulation/{$examAttempt->id}/ergebnis");
    }

    public function results(Request $request, int $attempt): Response|RedirectResponse
    {
        $examAttempt = $this->finder->find($request, $attempt);

        if ($examAttempt === null) {
            abort(404);
        }

        if (! $examAttempt->isSubmitted() && ! $examAttempt->hasExpired()) {
            return redirect("/pruefungssimulation/{$examAttempt->id}");
        }

        $this->autoSubmitIfNeeded($examAttempt);

        $total = $examAttempt->total_questions;
        $score = $examAttempt->score ?? 0;
        $passed = $total > 0 && ($score / $total) >= 0.60;

        return inertia('exam/results', [
            'attempt' => [
                'id' => $examAttempt->id,
                'score' => $score,
                'total_questions' => $total,
                'passed' => $passed,
                'submitted_at' => $examAttempt->submitted_at?->toIso8601String(),
                'is_claimed' => $examAttempt->user_id !== null,
            ],
            'topicBreakdown' => app(ExamScorer::class)->topicBreakdown($examAttempt),
        ]);
    }

    private function autoSubmitIfNeeded(ExamAttempt $attempt): void
    {
        if ($attempt->isSubmitted()) {
            return;
        }

        app(ExamScorer::class)->score($attempt);
        $attempt->update(['submitted_at' => now()]);
    }
}
