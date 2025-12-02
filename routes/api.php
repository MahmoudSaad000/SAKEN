<?php

use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);

// Admin-only route
Route::get('users', [UserController::class, 'getAllUsers'])->middleware('isAdmin');

// Bookings routes
Route::prefix('bookings')->group(function () {

    // Renter routes
    Route::middleware('isRenter')->group(function () {
        Route::apiResource('', BookingController::class);
        Route::put('{booking}/rate', [BookingController::class, 'rateBooking']);
    });

    // Admin routes
    Route::middleware('isAdmin')->group(function () {
        Route::get('all', [BookingController::class, 'getAllBookings']);
    });

    // Owner routes
    Route::middleware('isOwner')->group(function () {
        Route::get('unconfirmed', [BookingController::class, 'getUnConfirmedBookings']);
        Route::put('{booking_id}/confirm', [BookingController::class, 'confirmBooking']);
    });

    Route::apiResource('apartment',ApartmentController::class);

});
