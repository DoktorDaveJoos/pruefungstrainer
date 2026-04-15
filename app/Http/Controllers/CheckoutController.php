<?php

namespace App\Http\Controllers;

use App\Services\ExamAttemptFinder;
use App\Services\PolarCheckout;
use App\Services\PolarWebhookProcessor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Response as InertiaResponse;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly PolarCheckout $polarCheckout,
        private readonly PolarWebhookProcessor $webhookProcessor,
    ) {}

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

    public function success(Request $request): InertiaResponse
    {
        $user = $request->user();

        return inertia('checkout/success', [
            'isAuthenticated' => $user !== null,
            'isPaid' => $user?->isPaid() ?? false,
            'checkoutId' => $request->query('checkout_id'),
        ]);
    }

    public function webhook(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('webhook-signature');

        if ($signature === null || ! $this->webhookProcessor->verifySignature($payload, $signature)) {
            abort(401, 'Invalid signature');
        }

        $decoded = json_decode($payload, true);

        if (! is_array($decoded)) {
            abort(400, 'Malformed payload');
        }

        $this->webhookProcessor->process($decoded);

        return response('', 200);
    }
}
