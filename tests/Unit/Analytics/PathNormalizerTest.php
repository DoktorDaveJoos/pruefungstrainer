<?php

use App\Services\Analytics\PathNormalizer;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Tests\TestCase;

uses(TestCase::class);

it('returns the matched route uri prefixed with a slash', function (): void {
    $route = new Route(['GET'], '/pruefungssimulation/{attempt}', fn () => null);
    $route->bind(Request::create('/pruefungssimulation/42', 'GET'));
    $request = Request::create('/pruefungssimulation/42', 'GET');
    $request->setRouteResolver(fn () => $route);

    expect(app(PathNormalizer::class)->forRequest($request))
        ->toBe('/pruefungssimulation/{attempt}');
});

it('falls back to the raw path when no route is matched', function (): void {
    $request = Request::create('/no-route-here', 'GET');

    expect(app(PathNormalizer::class)->forRequest($request))
        ->toBe('/no-route-here');
});

it('truncates paths longer than 512 chars', function (): void {
    $long = '/x'.str_repeat('a', 600);
    $request = Request::create($long, 'GET');

    expect(strlen(app(PathNormalizer::class)->forRequest($request)))
        ->toBeLessThanOrEqual(512);
});
