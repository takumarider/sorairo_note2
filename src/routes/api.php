<?php

use App\Http\Controllers\Api\ReservationApiController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/reservations', [ReservationApiController::class, 'store']);
    Route::delete('/reservations/{reservation}', [ReservationApiController::class, 'destroy']);
});
