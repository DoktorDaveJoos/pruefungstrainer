<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveAnswerRequest;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Services\ExamAttemptFinder;
use App\Services\ExamDraw;
use App\Services\ExamScorer;
use App\Services\GuestStartGuard;
use App\Services\GuestStartStatus;
use App\Services\Pricing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Response;

class ExamController extends Controller
{
    private const FREE_RUN_COOKIE_TTL_MINUTES = 60 * 24 * 365;

    public function __construct(
        private readonly ExamDraw $examDraw,
        private readonly ExamAttemptFinder $finder,
        private readonly GuestStartGuard $guestStartGuard,
    ) {}

    public function start(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user === null) {
            $state = $this->guestStartGuard->inspect($request);

            if ($state->status === GuestStartStatus::Resume) {
                return redirect(route('exam.show', $state->attemptId));
            }

            if ($state->status === GuestStartStatus::AlreadyDone) {
                return redirect(route('home'));
            }
        }

        $sessionUuid = $user ? null : (string) Str::uuid();

        if ($user) {
            // Local dev shortcut: 3 questions instead of 50 so we can test faster.
            $total = app()->environment('local') ? 3 : 50;
            $questions = $this->examDraw->drawForUser(userId: $user->id, total: $total);
        } else {
            $questions = $this->examDraw->drawForGuest();
        }

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

        $response = redirect(route('exam.show', $attempt->id));

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

            return redirect(route('exam.results', $examAttempt->id));
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

        $response = redirect(route('exam.results', $examAttempt->id));

        if ($examAttempt->session_uuid !== null) {
            // Extend the guest cookie so the "already done" block survives beyond the 24 h start TTL.
            $response->withCookie(Cookie::make(
                ExamAttemptFinder::SESSION_COOKIE,
                $examAttempt->session_uuid,
                minutes: self::FREE_RUN_COOKIE_TTL_MINUTES,
            ));
        }

        return $response;
    }

    public function results(Request $request, int $attempt): Response|RedirectResponse
    {
        $examAttempt = $this->finder->find($request, $attempt);

        if ($examAttempt === null) {
            abort(404);
        }

        if (! $examAttempt->isSubmitted() && ! $examAttempt->hasExpired()) {
            return redirect(route('exam.show', $examAttempt->id));
        }

        $this->autoSubmitIfNeeded($examAttempt);

        $total = $examAttempt->total_questions;
        $score = $examAttempt->score ?? 0;
        $passed = $total > 0 && ($score / $total) >= 0.60;
        $hasAccess = (bool) $request->user()?->hasActiveAccess();

        return inertia('exam/results', [
            'attempt' => [
                'id' => $examAttempt->id,
                'score' => $hasAccess ? $score : null,
                'total_questions' => $total,
                'passed' => $passed,
                'submitted_at' => $examAttempt->submitted_at?->toIso8601String(),
                'is_claimed' => $examAttempt->user_id !== null,
            ],
            'topicBreakdown' => $hasAccess
                ? app(ExamScorer::class)->topicBreakdown($examAttempt)
                : null,
            'pricing' => app(Pricing::class)->currentPrice(),
            'hasAccess' => $hasAccess,
            'reviewItems' => $hasAccess ? $this->buildReviewItems($examAttempt) : null,
        ]);
    }

    /**
     * @return array<int, array{
     *     number: int,
     *     topic: string,
     *     stem: string,
     *     explanation: string,
     *     quote: string|null,
     *     source: string|null,
     *     options: array<int, array{text: string, isCorrect: bool, isUserChoice: bool}>,
     * }>
     */
    private function buildReviewItems(ExamAttempt $examAttempt): array
    {
        $examAttempt->loadMissing(['examAnswers.question.answers']);

        return $examAttempt->examAnswers
            ->where('is_correct', false)
            ->sortBy('position')
            ->values()
            ->map(function (ExamAnswer $ea): array {
                $question = $ea->question;
                $answersById = $question->answers->keyBy('id');
                $selected = $ea->selected_option_ids ?? [];

                $options = collect($ea->options_order)
                    ->map(function ($id) use ($answersById, $selected): array {
                        $answer = $answersById[$id];

                        return [
                            'text' => $answer->text,
                            'isCorrect' => (bool) $answer->is_correct,
                            'isUserChoice' => in_array($id, $selected, true),
                        ];
                    })
                    ->all();

                return [
                    'number' => $ea->position,
                    'topic' => $question->topic->label(),
                    'stem' => $question->text,
                    'explanation' => $question->explanation,
                    'quote' => $question->quote,
                    'source' => $question->source,
                    'options' => $options,
                ];
            })
            ->all();
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
