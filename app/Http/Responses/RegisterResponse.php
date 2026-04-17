<?php

namespace App\Http\Responses;

use App\Actions\ClaimGuestAttempt;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function __construct(private readonly ClaimGuestAttempt $claimGuestAttempt) {}

    public function toResponse($request): RedirectResponse
    {
        $user = $request->user();
        $claimed = ($this->claimGuestAttempt)($user, $request);

        // Pre-existing paid subscription (rare — re-registering with the same
        // email or creating a second account while still paid). Send them to
        // their claimed results if any, otherwise the default post-login page.
        if ($user->hasPaidOrder()) {
            if ($claimed?->isSubmitted()) {
                return redirect(route('exam.results', $claimed));
            }

            return redirect()->intended(config('fortify.home'));
        }

        // Default: every fresh registration goes straight to Polar checkout.
        // The whole app is behind the paywall, so there's nothing useful for
        // an unpaid account to do — bounce them to payment immediately.
        return redirect(route('checkout.start', absolute: false));
    }
}
