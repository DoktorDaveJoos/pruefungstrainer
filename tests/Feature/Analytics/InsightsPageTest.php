<?php

use App\Models\PageView;
use App\Models\TrackedEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Guests are redirected to /login by the auth middleware (302), not 404.
it('redirects guests to login', function (): void {
    $this->get('/admin/insights')->assertRedirect('/login');
});

it('404s for authenticated non-owners', function (): void {
    $user = User::factory()->create(['email' => 'random@example.com']);
    $this->actingAs($user)->get('/admin/insights')->assertNotFound();
});

it('renders insights for the owner', function (): void {
    PageView::create([
        'visitor_hash' => str_repeat('a', 64),
        'path' => '/',
        'referrer_host' => null,
        'user_id' => null,
        'created_at' => now(),
    ]);
    TrackedEvent::create([
        'name' => 'exam_started',
        'visitor_hash' => str_repeat('a', 64),
        'user_id' => null,
        'metadata' => ['attempt_id' => 1],
        'created_at' => now(),
    ]);

    $owner = User::factory()->create(['email' => config('app.owner_email')]);

    $this->actingAs($owner)
        ->get('/admin/insights')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/insights')
            ->has('overview.pageviewsToday')
            ->has('overview.examStartsToday')
            ->has('funnel')
            ->has('topPages')
            ->has('topReferrers')
            ->has('daily')
        );
});
