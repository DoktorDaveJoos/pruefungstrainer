<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class PolarCheckout
{
    /**
     * @param  array<string, mixed>  $metadata  arbitrary key/value pairs attached to the order; received back via webhook
     */
    public function createSession(array $metadata, ?string $customerEmail = null): string
    {
        $apiKey = config('services.polar.api_key');

        if (empty($apiKey)) {
            throw new RuntimeException('POLAR_API_KEY is not set');
        }

        $payload = [
            'product_id' => config('services.polar.product_id'),
            'success_url' => config('services.polar.success_url').'?checkout_id={CHECKOUT_ID}',
            'metadata' => $metadata,
        ];

        if ($customerEmail !== null) {
            $payload['customer_email'] = $customerEmail;
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$apiKey,
            'Content-Type' => 'application/json',
        ])->post(config('services.polar.base_url').'/checkouts', $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Polar checkout failed: '.$response->body());
        }

        $url = $response->json('url');

        if (! is_string($url) || $url === '') {
            throw new RuntimeException('Polar checkout returned no URL');
        }

        return $url;
    }
}
