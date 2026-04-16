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

        $productId = $pricing->currentProductId();

        abort_if(
            $productId === null,
            503,
            'Checkout ist noch nicht eingerichtet. Bitte setze POLAR_PRODUCT_FOUNDER und POLAR_PRODUCT_STANDARD in deiner .env.',
        );

        $url = $user->checkout([$productId])
            ->withSuccessUrl(route('checkout.processing').'?checkout_id={CHECKOUT_ID}')
            ->url();

        return Inertia::location($url);
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
