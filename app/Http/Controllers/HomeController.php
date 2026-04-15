<?php

namespace App\Http\Controllers;

use App\Services\Pricing;
use Inertia\Response;

class HomeController extends Controller
{
    public function __construct(private readonly Pricing $pricing) {}

    public function __invoke(): Response
    {
        return inertia('welcome', [
            'pricing' => $this->pricing->currentPrice(),
        ]);
    }
}
