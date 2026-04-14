<?php

use App\Models\Module;
use Illuminate\Database\UniqueConstraintViolationException;

it('creates a module via factory', function () {
    $module = Module::factory()->create();

    expect($module)->toBeInstanceOf(Module::class)
        ->and($module->name)->not->toBeEmpty()
        ->and($module->slug)->not->toBeEmpty();
});

it('enforces unique slugs', function () {
    Module::factory()->create(['slug' => 'duplicate-slug']);

    expect(fn () => Module::factory()->create(['slug' => 'duplicate-slug']))
        ->toThrow(UniqueConstraintViolationException::class);
});

it('resolves route key by slug', function () {
    $module = Module::factory()->create();

    expect($module->getRouteKeyName())->toBe('slug');
});
