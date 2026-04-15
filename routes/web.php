<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PracticeController;
use App\Http\Middleware\EnsurePaid;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::post('/pruefungssimulation/start', [ExamController::class, 'start'])->name('exam.start');
Route::get('/pruefungssimulation/{attempt}', [ExamController::class, 'show'])->name('exam.show');
Route::patch('/pruefungssimulation/{attempt}/answer/{position}', [ExamController::class, 'saveAnswer'])->name('exam.save-answer');
Route::post('/pruefungssimulation/{attempt}/submit', [ExamController::class, 'submit'])->name('exam.submit');
Route::get('/pruefungssimulation/{attempt}/ergebnis', [ExamController::class, 'results'])->name('exam.results');

Route::inertia('/agb', 'legal/agb')->name('legal.agb');
Route::inertia('/datenschutz', 'legal/datenschutz')->name('legal.datenschutz');
Route::inertia('/impressum', 'legal/impressum')->name('legal.impressum');

Route::post('/checkout/start', [CheckoutController::class, 'start'])->name('checkout.start');
Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
Route::post('/webhooks/polar', [CheckoutController::class, 'webhook'])->name('webhooks.polar');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth', 'verified', EnsurePaid::class])->group(function () {
    Route::get('/freies-lernen', [PracticeController::class, 'show'])->name('practice.show');
    Route::post('/freies-lernen/answer', [PracticeController::class, 'saveAnswer'])->name('practice.save-answer');
});

require __DIR__.'/settings.php';
