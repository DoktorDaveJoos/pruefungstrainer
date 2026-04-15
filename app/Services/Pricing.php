<?php

namespace App\Services;

use Danestves\LaravelPolar\Order;

class Pricing
{
    public const FOUNDER_PRICE_EUR = 29;

    public const STANDARD_PRICE_EUR = 49;

    public const FOUNDER_CAP = 100;

    /**
     * @return array{amount_eur: int, standard_price_eur: int, is_founder_price: bool, spots_remaining: int}
     */
    public function currentPrice(): array
    {
        $founderCount = Order::query()
            ->where('product_id', config('polar.products.founder'))
            ->whereNull('refunded_at')
            ->count();

        $isFounder = $founderCount < self::FOUNDER_CAP;

        return [
            'amount_eur' => $isFounder ? self::FOUNDER_PRICE_EUR : self::STANDARD_PRICE_EUR,
            'standard_price_eur' => self::STANDARD_PRICE_EUR,
            'is_founder_price' => $isFounder,
            'spots_remaining' => max(0, self::FOUNDER_CAP - $founderCount),
        ];
    }

    public function currentProductId(): ?string
    {
        return $this->currentPrice()['is_founder_price']
            ? config('polar.products.founder')
            : config('polar.products.standard');
    }
}
