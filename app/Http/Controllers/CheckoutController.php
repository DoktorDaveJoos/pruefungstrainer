<?php

namespace App\Http\Controllers;

use App\Actions\ClaimGuestAttempt;
use App\Models\ExamAttempt;
use App\Models\User;
use App\Services\Pricing;
use Danestves\LaravelPolar\Exceptions\InvalidCustomer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class CheckoutController extends Controller
{
    public function __construct(private readonly ClaimGuestAttempt $claimGuestAttempt) {}

    public function start(Request $request, Pricing $pricing)
    {
        $user = $request->user();

        // Real-order check (not hasActiveAccess) — otherwise the local paywall
        // bypass would stop us from ever reaching Polar in development.
        if ($user->hasPaidOrder()) {
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
        $user = $request->user();
        ($this->claimGuestAttempt)($user, $request);

        $hasAccess = (bool) $user->hasActiveAccess();

        return Inertia::render('checkout/processing', [
            'hasAccess' => $hasAccess,
            'redirectTo' => $hasAccess ? $this->resolveRedirectTo($request, $user) : null,
        ]);
    }

    public function portal(Request $request): HttpResponse
    {
        $user = $request->user();

        try {
            $url = $user->customerPortalUrl();
        } catch (InvalidCustomer) {
            return redirect()->route('checkout.start');
        }

        return Inertia::location($url);
    }

    public function accessStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        ($this->claimGuestAttempt)($user, $request);

        $hasAccess = (bool) $user->hasActiveAccess();

        return response()->json([
            'hasAccess' => $hasAccess,
            'redirectTo' => $hasAccess ? $this->resolveRedirectTo($request, $user) : null,
        ]);
    }

    private function resolveRedirectTo(Request $request, User $user): string
    {
        $attemptId = ClaimGuestAttempt::rememberedAttemptId($request)
            ?? ExamAttempt::query()
                ->where('user_id', $user->id)
                ->whereNotNull('claimed_at')
                ->whereNotNull('submitted_at')
                ->latest('claimed_at')
                ->value('id');

        return $attemptId
            ? route('exam.results', $attemptId, absolute: false)
            : config('fortify.home');
    }
}
