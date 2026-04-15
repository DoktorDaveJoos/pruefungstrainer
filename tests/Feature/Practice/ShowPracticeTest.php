<?php

use App\Enums\BsiTopic;
use App\Enums\QuestionDifficulty;
use App\Models\Answer;
use App\Models\Module;
use App\Models\PracticeAnswer;
use App\Models\Question;
use App\Models\User;

beforeEach(function () {
    $this->module = Module::factory()->create(['slug' => 'm2-bsi-grundschutz']);
});

it('renders the practice page with a question for a paid user', function () {
    $user = User::factory()->hasActiveAccess()->create();
    $q = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    Answer::factory()->for($q)->count(4)->create();

    $response = $this->actingAs($user)->get('/freies-lernen');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('freies-lernen/index')
        ->has('question.id')
        ->has('question.options', 4)
        ->has('progress.seen')
        ->has('progress.total')
        ->has('progress.correct')
        ->where('wrongOnly', false)
    );
});

it('passes wrongOnly query param through to props', function () {
    $user = User::factory()->hasActiveAccess()->create();
    $q = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    Answer::factory()->for($q)->correct()->create();
    PracticeAnswer::factory()->for($user)->for($q)->incorrect([1])->create();

    $response = $this->actingAs($user)->get('/freies-lernen?wrong_only=1');

    $response->assertInertia(fn ($page) => $page
        ->where('wrongOnly', true)
        ->where('question.id', $q->id)
    );
});

it('honors exclude query param', function () {
    $user = User::factory()->hasActiveAccess()->create();
    $a = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    $b = Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();
    Answer::factory()->for($a)->count(2)->create();
    Answer::factory()->for($b)->count(2)->create();

    $response = $this->actingAs($user)->get("/freies-lernen?exclude={$a->id}");

    $response->assertInertia(fn ($page) => $page->where('question.id', $b->id));
});

it('renders an empty state when no question is available', function () {
    $user = User::factory()->hasActiveAccess()->create();
    Question::factory()->for($this->module)->tagged(BsiTopic::Methodik, QuestionDifficulty::Basis)->create();

    $response = $this->actingAs($user)->get('/freies-lernen?wrong_only=1');

    $response->assertInertia(fn ($page) => $page
        ->component('freies-lernen/index')
        ->where('question', null)
        ->where('wrongOnly', true)
    );
});

it('returns 403 for an unpaid user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/freies-lernen')->assertStatus(403);
});

it('redirects unauthenticated user to login', function () {
    $this->get('/freies-lernen')->assertRedirect('/login');
});
