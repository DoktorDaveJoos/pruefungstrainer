<?php

namespace App\Services;

use App\Models\User;

class Pricing
{
    private const FOUNDER_PRICE_EUR = 29;

    private const STANDARD_PRICE_EUR = 49;

    private const FOUNDER_CAP = 100;

    /**
     * @return array{amount_eur: int, is_founder_price: bool, spots_remaining: int}
     */
    public function currentPrice(): array
    {
        $paidCount = User::whereNotNull('paid_at')->count();

        $isFounder = $paidCount < self::FOUNDER_CAP;
        $spotsRemaining = max(0, self::FOUNDER_CAP - $paidCount);

        return [
            'amount_eur' => $isFounder ? self::FOUNDER_PRICE_EUR : self::STANDARD_PRICE_EUR,
            'is_founder_price' => $isFounder,
            'spots_remaining' => $spotsRemaining,
        ];
    }
}
