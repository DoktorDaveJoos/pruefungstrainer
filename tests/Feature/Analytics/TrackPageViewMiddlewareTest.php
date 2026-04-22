<?php

use App\Models\ExamAttempt;
use App\Models\PageView;
use App\Models\User;
use App\Services\ExamAttemptFinder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('records a page view for a GET html response', function (): void {
    $this->get('/')->assertOk();

    expect(PageView::count())->toBe(1);

    $row = PageView::first();
    expect($row->path)->toBe('/');
    expect($row->visitor_hash)->toMatch('/^[0-9a-f]{64}$/');
    expect($row->user_id)->toBeNull();
});

it('skips non-GET requests', function (): void {
    $this->post('/pruefungssimulation/start');

    expect(PageView::count())->toBe(0);
});

it('skips the admin insights path', function (): void {
    $user = User::factory()->create([
        'email' => config('app.owner_emails')[0] ?? 'owner@test.test',
    ]);
    $this->actingAs($user)->get('/admin/insights');

    expect(PageView::where('path', '/admin/insights')->count())->toBe(0);
});

it('skips bot user agents', function (): void {
    $this->withHeaders(['User-Agent' => 'Googlebot/2.1'])->get('/');

    expect(PageView::count())->toBe(0);
});

it('tags user_id when authenticated', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/');

    expect(PageView::first()?->user_id)->toBe($user->id);
});

it('normalizes dynamic route segments', function (): void {
    $attempt = ExamAttempt::factory()->create();

    $this->withCookie(ExamAttemptFinder::SESSION_COOKIE, $attempt->session_uuid)
        ->get("/pruefungssimulation/{$attempt->id}");

    $row = PageView::first();
    expect($row?->path)->toBe('/pruefungssimulation/{attempt}');
});
