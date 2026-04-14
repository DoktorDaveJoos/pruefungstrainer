<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveAnswerRequest;
use App\Models\ExamAttempt;
use App\Services\ExamAttemptFinder;
use App\Services\ExamDraw;
use App\Services\ExamScorer;
use Illuminate\Http\JsonResponse;
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
            ->map(fn ($ea) => [
                'position' => $ea->position,
                'question_id' => $ea->question_id,
                'text' => $ea->question->text,
                'options' => $ea->question->answers
                    ->shuffle()
                    ->map(fn ($a) => ['id' => $a->id, 'text' => $a->text])
                    ->values(),
                'selected_option_ids' => $ea->selected_option_ids ?? [],
                'flagged' => $ea->flagged,
            ]);

        return inertia('exam/question', [
            'attempt' => [
                'id' => $examAttempt->id,
                'timer_expires_at' => $examAttempt->timer_expires_at->toIso8601String(),
                'total_questions' => $examAttempt->total_questions,
            ],
            'questions' => $questions,
        ]);
    }

    public function saveAnswer(SaveAnswerRequest $request, int $attempt, int $position): JsonResponse
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

        return response()->json(['saved' => true]);
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
