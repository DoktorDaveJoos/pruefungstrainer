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
        $claimed = ($this->claimGuestAttempt)($request->user(), $request);

        if ($claimed?->isSubmitted() || $request->query('intent') === 'checkout') {
            return redirect(route('checkout.start', absolute: false));
        }

        return redirect()->intended(config('fortify.home'));
    }
}
