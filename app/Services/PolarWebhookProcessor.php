<?php

namespace App\Services;

use App\Models\ExamAttempt;
use App\Models\User;
use Illuminate\Support\Str;

class PolarWebhookProcessor
{
    public function verifySignature(string $payload, string $signatureHeader): bool
    {
        $secret = config('services.polar.webhook_secret');

        if (empty($secret)) {
            return false;
        }

        $expected = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expected, $signatureHeader);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function process(array $payload): void
    {
        if (($payload['type'] ?? null) !== 'order.created') {
            return;
        }

        $data = $payload['data'] ?? [];
        $customer = $data['customer'] ?? [];
        $metadata = $data['metadata'] ?? [];

        $user = $this->findOrCreateUser($customer, $metadata);

        if ($user->paid_at === null) {
            $user->update([
                'paid_at' => now(),
                'polar_customer_id' => $customer['id'] ?? $user->polar_customer_id,
            ]);
        } elseif ($user->polar_customer_id === null && isset($customer['id'])) {
            $user->update(['polar_customer_id' => $customer['id']]);
        }

        $this->claimAnonymousAttempt($user, $metadata);
    }

    /**
     * @param  array<string, mixed>  $customer
     * @param  array<string, mixed>  $metadata
     */
    private function findOrCreateUser(array $customer, array $metadata): User
    {
        if (isset($metadata['user_id'])) {
            $user = User::find((int) $metadata['user_id']);
            if ($user !== null) {
                return $user;
            }
        }

        $email = $customer['email'] ?? null;

        if ($email === null) {
            throw new \RuntimeException('Polar webhook: no user_id metadata and no customer email');
        }

        $existing = User::where('email', $email)->first();

        if ($existing !== null) {
            return $existing;
        }

        return User::create([
            'name' => $customer['name'] ?? explode('@', $email)[0],
            'email' => $email,
            'password' => bcrypt(Str::random(32)),
            'polar_customer_id' => $customer['id'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function claimAnonymousAttempt(User $user, array $metadata): void
    {
        $sessionUuid = $metadata['session_uuid'] ?? null;

        if ($sessionUuid === null) {
            return;
        }

        ExamAttempt::where('session_uuid', $sessionUuid)
            ->whereNull('user_id')
            ->update(['user_id' => $user->id]);
    }
}
