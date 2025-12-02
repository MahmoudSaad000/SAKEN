<?php

use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');





Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);
Route::post('logout', [UserController::class, 'logout'])->middleware('auth:sanctum');



Route::middleware('auth:sanctum')->group(function () {

    Route::get('users', [UserController::class, 'getAllUsers'])->middleware('isAdmin');

        Route::middleware('isRenter')->group(function () {
            Route::apiResource('/bookings', BookingController::class);
            Route::put('bookings/{booking}/rate', [BookingController::class, 'rateBooking']);
        });


    Route::prefix('/bookings')->group(function () {
        Route::middleware('isAdmin')->group(function () {
            Route::get('all', [BookingController::class, 'getAllBookings']);
        });

        Route::middleware('isOwner')->group(function () {
            Route::get('{apartment}/unconfirmed', [BookingController::class, 'getUnConfirmedBookings']);
            Route::put('{booking}/confirm', [BookingController::class, 'confirmBooking']);
            Route::put('{booking}/reject', [BookingController::class, 'rejectBooking']);
        });
    });


    Route::prefix('/apartment')->group(function () {

        Route::apiResource('', ApartmentController::class);
    });
});
