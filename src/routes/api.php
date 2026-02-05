<?php

use App\Http\Controllers\Api\ReservationApiController;
use App\Http\Controllers\Api\SlotApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/slots', [SlotApiController::class, 'index']);
    Route::post('/reservations', [ReservationApiController::class, 'store']);
    Route::delete('/reservations/{reservation}', [ReservationApiController::class, 'destroy']);
});
