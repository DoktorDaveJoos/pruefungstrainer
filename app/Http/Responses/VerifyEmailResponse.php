<?php

namespace App\Http\Responses;

use App\Actions\ClaimGuestAttempt;
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

    private function claimedResultsUrl(Request $request): ?string
    {
        $user = $request->user();

        if ($user === null) {
            return null;
        }

        $attemptId = ClaimGuestAttempt::resolveClaimedAttemptId($user, $request);

        return $attemptId
            ? route('exam.results', $attemptId, absolute: false).'?verified=1'
            : null;
    }
}
