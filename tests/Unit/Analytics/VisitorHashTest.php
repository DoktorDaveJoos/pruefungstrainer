<?php

use App\Services\Analytics\VisitorHash;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Tests\TestCase;

uses(TestCase::class);

it('produces a 64-character hex hash', function (): void {
    $request = Request::create('/', 'GET', server: [
        'REMOTE_ADDR' => '203.0.113.10',
        'HTTP_USER_AGENT' => 'Mozilla/5.0',
    ]);

    $hash = app(VisitorHash::class)->for($request);

    expect($hash)->toMatch('/^[0-9a-f]{64}$/');
});

it('is stable for the same ip+ua on the same day', function (): void {
    $first = Request::create('/', 'GET', server: [
        'REMOTE_ADDR' => '203.0.113.10',
        'HTTP_USER_AGENT' => 'Mozilla/5.0',
    ]);
    $second = Request::create('/exam', 'GET', server: [
        'REMOTE_ADDR' => '203.0.113.10',
        'HTTP_USER_AGENT' => 'Mozilla/5.0',
    ]);

    $service = app(VisitorHash::class);

    expect($service->for($first))->toBe($service->for($second));
});

it('rotates daily', function (): void {
    $request = Request::create('/', 'GET', server: [
        'REMOTE_ADDR' => '203.0.113.10',
        'HTTP_USER_AGENT' => 'Mozilla/5.0',
    ]);
    $service = app(VisitorHash::class);

    $today = Carbon::parse('2026-04-22 10:00:00');
    $tomorrow = $today->copy()->addDay();

    Carbon::setTestNow($today);
    $a = $service->for($request);

    Carbon::setTestNow($tomorrow);
    $b = $service->for($request);

    expect($a)->not->toBe($b);
});

it('differs for different user agents', function (): void {
    $a = Request::create('/', 'GET', server: [
        'REMOTE_ADDR' => '203.0.113.10',
        'HTTP_USER_AGENT' => 'Mozilla/5.0',
    ]);
    $b = Request::create('/', 'GET', server: [
        'REMOTE_ADDR' => '203.0.113.10',
        'HTTP_USER_AGENT' => 'curl/8.0',
    ]);
    $service = app(VisitorHash::class);

    expect($service->for($a))->not->toBe($service->for($b));
});
