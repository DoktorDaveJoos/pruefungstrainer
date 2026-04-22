<?php

use App\Models\TrackedEvent;
use App\Models\User;
use App\Services\Analytics\RecordEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('persists an event with the given name and metadata', function (): void {
    app(RecordEvent::class)->record('exam_started', metadata: ['attempt_id' => 42]);

    expect(TrackedEvent::count())->toBe(1);

    $event = TrackedEvent::first();
    expect($event->name)->toBe('exam_started');
    expect($event->metadata)->toBe(['attempt_id' => 42]);
    expect($event->user_id)->toBeNull();
    expect($event->visitor_hash)->toBeNull();
});

it('attaches the authenticated user id', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    app(RecordEvent::class)->record('registered');

    expect(TrackedEvent::first()->user_id)->toBe($user->id);
});

it('attaches a visitor hash when an http request is in scope', function (): void {
    $this->get('/');

    app(RecordEvent::class)->record('exam_started', metadata: ['attempt_id' => 1]);

    expect(TrackedEvent::first()->visitor_hash)->toMatch('/^[0-9a-f]{64}$/');
});

it('skips the visitor hash when includeVisitorHash is false', function (): void {
    $this->get('/');

    app(RecordEvent::class)->record('paid', metadata: ['order_id' => 'xyz'], includeVisitorHash: false);

    $event = TrackedEvent::where('name', 'paid')->first();
    expect($event->visitor_hash)->toBeNull();
    expect($event->metadata)->toBe(['order_id' => 'xyz']);
});
