<?php

namespace App\Models;

use Danestves\LaravelPolar\Billable;
use Danestves\LaravelPolar\Order;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

#[Fillable(['name', 'email', 'password'])]
class User extends Authenticatable implements MustVerifyEmail
{
    use Billable, HasFactory, HasTimestamps, Notifiable, TwoFactorAuthenticatable;

    protected $hidden = ['password', 'remember_token', 'two_factor_recovery_codes', 'two_factor_secret'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function practiceAnswers(): HasMany
    {
        return $this->hasMany(PracticeAnswer::class);
    }

    /**
     * "Can the user see paid content?" — used by results unlock, feature gates,
     * and post-purchase polling. Honors the local paywall bypass so we can
     * preview the unlocked UX without a tunnelled Polar webhook.
     */
    public function hasActiveAccess(): bool
    {
        if (app()->environment('local')) {
            return true;
        }

        return $this->hasPaidOrder();
    }

    /**
     * "Does the user have a real paid Order?" — the authoritative commerce
     * check, with no local bypass. Use this to decide whether to skip the
     * checkout flow; otherwise local users would never reach Polar.
     */
    public function hasPaidOrder(): bool
    {
        return Order::query()
            ->where('billable_id', $this->id)
            ->where('billable_type', self::class)
            ->whereIn('product_id', [
                config('polar.products.founder'),
                config('polar.products.standard'),
            ])
            ->whereNull('refunded_at')
            ->where('ordered_at', '>=', now()->subYear())
            ->exists();
    }
}
