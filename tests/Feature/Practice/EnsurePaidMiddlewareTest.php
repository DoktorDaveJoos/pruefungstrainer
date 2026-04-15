<?php

use App\Http\Middleware\EnsurePaid;
use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::get('/_test/paid-only', fn () => 'ok')->middleware(['auth', EnsurePaid::class]);
});

it('allows a paid user through', function () {
    $user = User::factory()->paid()->create();

    $this->actingAs($user)
        ->get('/_test/paid-only')
        ->assertStatus(200);
});

it('returns 403 for an unpaid authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/_test/paid-only')
        ->assertStatus(403);
});

it('redirects an unauthenticated user to login (auth middleware fires first)', function () {
    $this->get('/_test/paid-only')
        ->assertRedirect('/login');
});
