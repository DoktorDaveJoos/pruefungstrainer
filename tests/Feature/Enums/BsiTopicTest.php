<?php

use App\Enums\BsiTopic;

it('has exactly 8 cases', function () {
    expect(BsiTopic::cases())->toHaveCount(8);
});

it('exposes a German label for each case', function (BsiTopic $topic) {
    expect($topic->label())->toBeString()->not->toBeEmpty();
})->with(BsiTopic::cases());

it('uses stable string backing values', function () {
    $expected = ['methodik', 'bausteine', 'risikoanalyse', 'modellierung', 'check', 'standards', 'notfall', 'siem'];
    expect(array_map(fn (BsiTopic $t) => $t->value, BsiTopic::cases()))->toBe($expected);
});
