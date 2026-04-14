<?php

use App\Enums\QuestionDifficulty;

it('has exactly 2 cases', function () {
    expect(QuestionDifficulty::cases())->toHaveCount(2);
});

it('maps Basis → basis and Experte → experte', function () {
    expect(QuestionDifficulty::Basis->value)->toBe('basis');
    expect(QuestionDifficulty::Experte->value)->toBe('experte');
});

it('exposes a German label for each case', function () {
    expect(QuestionDifficulty::Basis->label())->toBe('Basis');
    expect(QuestionDifficulty::Experte->label())->toBe('Experte');
});
