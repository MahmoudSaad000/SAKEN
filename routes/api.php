<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {

    Route::get('users', [UserController::class, 'getAllUsers'])->middleware('isAdmin');


    Route::prefix('/bookings')->group(function () {
        Route::middleware('isRenter')->group(function () {
            Route::apiResource('', BookingController::class);
            Route::put('{booking}/rate', [BookingController::class, 'rateBooking']);
        });


    Route::prefix('/bookings')->group(function () {
        Route::middleware('isAdmin')->group(function () {

            Route::get('/all', [BookingController::class, 'getAllBookings'])->middleware('isAdmin');
        });

        Route::middleware('isOwner')->group(function () {

            Route::get('/unconfirmed', [BookingController::class, 'getUnConfirmedBookings']);
            Route::put('/{booking_id}/confirm', [BookingController::class, 'confirmBooking']);
        });
    });
});
