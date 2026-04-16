<?php

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        if ($request->query('intent') === 'checkout') {
            $user = $request->user();

            if ($user && $user->hasActiveAccess()) {
                return redirect()->intended(config('fortify.home'));
            }

            return redirect('/checkout/start');
        }

        return redirect()->intended(config('fortify.home'));
    }
}
