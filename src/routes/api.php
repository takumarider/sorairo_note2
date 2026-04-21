<?php

use App\Http\Controllers\Api\ReservationApiController;
use App\Http\Controllers\Api\TimeBlockApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth:web'])->group(function () {
    Route::get('/reservations/events', [ReservationApiController::class, 'events']);
    Route::post('/reservations', [ReservationApiController::class, 'store']);
    Route::delete('/reservations/{reservation}', [ReservationApiController::class, 'destroy']);

    Route::get('/time-blocks', [TimeBlockApiController::class, 'index']);
    Route::post('/time-blocks', [TimeBlockApiController::class, 'store']);
    Route::delete('/time-blocks/{timeBlock}', [TimeBlockApiController::class, 'destroy']);
});
