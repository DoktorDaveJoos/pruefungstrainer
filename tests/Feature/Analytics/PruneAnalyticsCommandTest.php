<?php

use App\Models\PageView;
use App\Models\TrackedEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('deletes rows older than 90 days and keeps younger rows', function (): void {
    PageView::create([
        'visitor_hash' => str_repeat('a', 64),
        'path' => '/',
        'referrer_host' => null,
        'user_id' => null,
        'created_at' => now()->subDays(91),
    ]);
    PageView::create([
        'visitor_hash' => str_repeat('b', 64),
        'path' => '/',
        'referrer_host' => null,
        'user_id' => null,
        'created_at' => now()->subDays(30),
    ]);
    TrackedEvent::create([
        'name' => 'paid',
        'visitor_hash' => null,
        'user_id' => null,
        'metadata' => null,
        'created_at' => now()->subDays(95),
    ]);
    TrackedEvent::create([
        'name' => 'paid',
        'visitor_hash' => null,
        'user_id' => null,
        'metadata' => null,
        'created_at' => now()->subDays(10),
    ]);

    $this->artisan('analytics:prune')->assertSuccessful();

    expect(PageView::count())->toBe(1);
    expect(TrackedEvent::count())->toBe(1);
});
