<?php

namespace App\Http\Controllers;

use App\Services\GuestStartGuard;
use App\Services\GuestStartStatus;
use App\Services\Pricing;
use Illuminate\Http\Request;
use Inertia\Response;

class HomeController extends Controller
{
    public function __construct(
        private readonly Pricing $pricing,
        private readonly GuestStartGuard $guestStartGuard,
    ) {}

    public function __invoke(Request $request): Response
    {
        $state = $request->user() === null
            ? $this->guestStartGuard->inspect($request)
            : null;

        return inertia('welcome', [
            'pricing' => $this->pricing->currentPrice(),
            'freeTier' => [
                'status' => ($state?->status ?? GuestStartStatus::Available)->value,
                'lastAttemptId' => $state?->attemptId,
            ],
        ]);
    }
}
