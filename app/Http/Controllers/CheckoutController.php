<?php

namespace App\Http\Controllers;

use App\Services\ExamAttemptFinder;
use App\Services\PolarCheckout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(private readonly PolarCheckout $polarCheckout) {}

    public function start(Request $request): RedirectResponse
    {
        $user = $request->user();
        $metadata = [];

        if ($user) {
            $metadata['user_id'] = $user->id;
            $email = $user->email;
        } else {
            $sessionUuid = $request->cookie(ExamAttemptFinder::SESSION_COOKIE);
            if ($sessionUuid !== null) {
                $metadata['session_uuid'] = $sessionUuid;
            }
            $email = null;
        }

        try {
            $url = $this->polarCheckout->createSession($metadata, $email);
        } catch (RuntimeException $e) {
            abort(503, 'Checkout temporarily unavailable: '.$e->getMessage());
        }

        return redirect()->away($url);
    }
}
