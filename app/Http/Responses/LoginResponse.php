<?php

namespace App\Http\Responses;

use App\Actions\ClaimGuestAttempt;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function __construct(private readonly ClaimGuestAttempt $claimGuestAttempt) {}

    public function toResponse($request): RedirectResponse
    {
        $user = $request->user();
        $claimed = ($this->claimGuestAttempt)($user, $request);

        if ($claimed?->isSubmitted()) {
            return redirect(route('exam.results', $claimed));
        }

        if ($request->query('intent') === 'checkout') {
            // Real-order check (not hasActiveAccess) so local users without a
            // paid order still get routed through Polar.
            if ($user->hasPaidOrder()) {
                return redirect()->intended(config('fortify.home'));
            }

            return redirect(route('checkout.start', absolute: false));
        }

        return redirect()->intended(config('fortify.home'));
    }
}
