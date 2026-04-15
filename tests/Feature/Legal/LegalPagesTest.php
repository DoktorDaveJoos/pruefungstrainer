<?php

it('renders /agb', function () {
    $this->get('/agb')->assertStatus(200)->assertInertia(fn ($page) => $page->component('legal/agb'));
});

it('renders /datenschutz', function () {
    $this->get('/datenschutz')->assertStatus(200)->assertInertia(fn ($page) => $page->component('legal/datenschutz'));
});

it('renders /impressum', function () {
    $this->get('/impressum')->assertStatus(200)->assertInertia(fn ($page) => $page->component('legal/impressum'));
});
