<?php

namespace App\Http\Responses;

use App\Actions\ClaimGuestAttempt;
use App\Models\ExamAttempt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;
use Laravel\Fortify\Fortify;

class VerifyEmailResponse implements VerifyEmailResponseContract
{
    public function toResponse($request): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 204);
        }

        $target = $this->claimedResultsUrl($request)
            ?? Fortify::redirects('email-verification').'?verified=1';

        return redirect()->intended($target);
    }

    /**
     * If the user registered via the unlock-drawer (or claimed a guest attempt
     * during checkout), their payment flow paused at verification. Send them
     * straight back to the results page they were trying to unlock.
     */
    private function claimedResultsUrl(Request $request): ?string
    {
        $user = $request->user();

        if ($user === null) {
            return null;
        }

        $attemptId = ClaimGuestAttempt::rememberedAttemptId($request)
            ?? ExamAttempt::query()
                ->where('user_id', $user->id)
                ->whereNotNull('claimed_at')
                ->whereNotNull('submitted_at')
                ->latest('claimed_at')
                ->value('id');

        return $attemptId
            ? route('exam.results', $attemptId, absolute: false).'?verified=1'
            : null;
    }
}
