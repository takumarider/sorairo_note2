<?php

use App\Http\Controllers\MenuController;
use App\Http\Controllers\MypageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SlotController;
use App\Models\Note;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $notes = Note::where('is_published', true)
        ->orderByDesc('published_at')
        ->orderByDesc('created_at')
        ->take(10)
        ->get();

    return view('dashboard', compact('notes'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/menus', [MenuController::class, 'index'])->name('menus.index');
    Route::get('/menus/{menu}', [MenuController::class, 'show'])->name('menus.show');

    Route::get('/slots', [SlotController::class, 'index'])->name('slots.index');

    Route::get('/reservations/confirm', [ReservationController::class, 'confirm'])->name('reservations.confirm');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
    Route::get('/reservations/{reservation}/complete', [ReservationController::class, 'complete'])->name('reservations.complete');

    Route::get('/mypage', [MypageController::class, 'index'])->name('mypage');
    Route::delete('/reservations/{reservation}', [MypageController::class, 'cancel'])->name('reservations.cancel');
});

require __DIR__.'/auth.php';
