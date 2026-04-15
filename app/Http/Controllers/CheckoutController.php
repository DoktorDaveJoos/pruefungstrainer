<?php

namespace App\Http\Controllers;

use App\Services\Pricing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function start(Request $request, Pricing $pricing)
    {
        $user = $request->user();

        if ($user->hasActiveAccess()) {
            return redirect()->intended(config('fortify.home'));
        }

        return $user->checkout([$pricing->currentProductId()])
            ->withSuccessUrl(route('checkout.processing').'?checkout_id={CHECKOUT_ID}');
    }

    public function processing(Request $request): Response
    {
        return Inertia::render('checkout/processing', [
            'hasAccess' => (bool) $request->user()?->hasActiveAccess(),
        ]);
    }

    public function accessStatus(Request $request): JsonResponse
    {
        return response()->json(['hasAccess' => (bool) $request->user()?->hasActiveAccess()]);
    }
}
