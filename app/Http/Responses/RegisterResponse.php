<?php

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        if ($request->query('intent') === 'checkout') {
            return redirect('/checkout/start');
        }

        return redirect()->intended(config('fortify.home'));
    }
}
