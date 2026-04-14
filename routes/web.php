<?php

use App\Http\Controllers\ExamController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::post('/pruefungssimulation/start', [ExamController::class, 'start'])->name('exam.start');
Route::get('/pruefungssimulation/{attempt}', [ExamController::class, 'show'])->name('exam.show');
Route::patch('/pruefungssimulation/{attempt}/answer/{position}', [ExamController::class, 'saveAnswer'])->name('exam.save-answer');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
