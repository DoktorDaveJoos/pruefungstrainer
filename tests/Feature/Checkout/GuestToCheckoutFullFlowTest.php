<?php

use App\Actions\ClaimGuestAttempt;
use App\Models\ExamAttempt;
use App\Models\User;
use App\Services\ExamAttemptFinder;
use Danestves\LaravelPolar\LaravelPolar;
use Danestves\LaravelPolar\Order;
use Illuminate\Support\Str;

/**
 * End-to-end coverage for: guest takes free exam, submits, opens checkout
 * drawer, registers, is routed through Polar, and on return from Polar lands
 * on the (newly claimed) results page — NOT the dashboard.
 */
function simulatePaidWebhookFor(int $userId): void
{
    Order::factory()->create([
        'billable_id' => $userId,
        'billable_type' => User::class,
        'product_id' => config('polar.products.founder'),
        'ordered_at' => now()->subMinute(),
        'refunded_at' => null,
    ]);
}

afterEach(function () {
    LaravelPolar::resetSdk();
});

it('guest → register → checkout → polar webhook → processing sends unverified user to email verification, then back to claimed results', function () {
    LaravelPolar::setSdk(mockPolarSdk());

    $uuid = Str::uuid()->toString();
    $attempt = ExamAttempt::factory()->submitted(30)->create([
        'user_id' => null,
        'session_uuid' => $uuid,
    ]);

    // 1. Guest registers with the exam session cookie present.
    $register = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, $uuid)
        ->post('/register?intent=checkout', [
            'name' => 'Test',
            'email' => 'e2e@example.com',
            'password' => 'password1234',
            'password_confirmation' => 'password1234',
        ]);

    $register->assertRedirect('/checkout/start');

    // Claim fires in RegisterResponse before the redirect.
    $attempt->refresh();
    expect($attempt->user_id)->not->toBeNull()
        ->and($attempt->claimed_at)->not->toBeNull();

    // 2. Follow to /checkout/start — unpaid → redirect to Polar sandbox.
    $this->get('/checkout/start')->assertRedirect('https://sandbox.polar.sh/checkout/chk_test');

    // 3. Polar webhook fires in the background, creating the paid Order.
    simulatePaidWebhookFor($attempt->fresh()->user_id);

    // 4. Polar redirects the user back to /checkout/processing. User has paid
    // but not yet verified their email → processing hands off to the
    // verification screen instead of the locked results page.
    $processing = $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, $uuid)
        ->get('/checkout/processing?checkout_id=chk_test');

    $processing->assertStatus(200);
    $processing->assertInertia(fn ($page) => $page
        ->component('checkout/processing')
        ->where('hasAccess', true)
        ->where('redirectTo', '/email/verify')
    );
});

it('processing trusts the session-stashed claimed attempt id even if the DB claim row is missing', function () {
    // Guards against a real-world failure mode we saw in the browser: the
    // user registered, went to Polar, and came back with no pt_exam_session
    // cookie AND no claimed_at in the DB — leaving resolveRedirectTo unable
    // to pick a target. The session fallback pins the redirect target at
    // claim-time so this can't happen.
    $user = User::factory()->create();
    simulatePaidWebhookFor($user->id);

    // Attempt is owned by the user + submitted — but claimed_at is null,
    // which is what the old resolveRedirectTo DB query required.
    $attempt = ExamAttempt::factory()->submitted(30)->create([
        'user_id' => $user->id,
        'claimed_at' => null,
        'session_uuid' => Str::uuid()->toString(),
    ]);

    $this->actingAs($user)
        ->withSession([ClaimGuestAttempt::SESSION_KEY => $attempt->id])
        ->get('/checkout/processing?checkout_id=chk_test')
        ->assertInertia(fn ($page) => $page
            ->component('checkout/processing')
            ->where('hasAccess', true)
            ->where('redirectTo', "/pruefungssimulation/{$attempt->id}/ergebnis")
        );
});

it('guest → register → processing routes unverified user to email verification even if pt_exam_session is missing on return', function () {
    LaravelPolar::setSdk(mockPolarSdk());

    $uuid = Str::uuid()->toString();
    $attempt = ExamAttempt::factory()->submitted(30)->create([
        'user_id' => null,
        'session_uuid' => $uuid,
    ]);

    // Register with the cookie present (claim happens now).
    $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, $uuid)
        ->post('/register?intent=checkout', [
            'name' => 'Test',
            'email' => 'no-cookie@example.com',
            'password' => 'password1234',
            'password_confirmation' => 'password1234',
        ])
        ->assertRedirect('/checkout/start');

    // Polar webhook arrives during the user's Polar visit.
    simulatePaidWebhookFor($attempt->fresh()->user_id);

    // Return from Polar WITHOUT the pt_exam_session cookie. Paid but
    // unverified → verification screen takes over; the claimed attempt is
    // handed back to the user after they verify (see VerifyEmailResponse test).
    $processing = $this->get('/checkout/processing?checkout_id=chk_test');

    $processing->assertInertia(fn ($page) => $page
        ->component('checkout/processing')
        ->where('hasAccess', true)
        ->where('redirectTo', '/email/verify')
    );
});
