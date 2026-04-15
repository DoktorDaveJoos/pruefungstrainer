<?php

use App\Models\User;

it('returns hasAccess=false for users without orders', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
        ->getJson('/api/access-status')
        ->assertOk()
        ->assertJson(['hasAccess' => false]);
});

it('returns hasAccess=true for users with active orders', function () {
    $user = User::factory()->hasActiveAccess()->create();
    $this->actingAs($user)
        ->getJson('/api/access-status')
        ->assertOk()
        ->assertJson(['hasAccess' => true]);
});

it('returns 401 for unauthenticated requests', function () {
    $this->getJson('/api/access-status')->assertUnauthorized();
});
