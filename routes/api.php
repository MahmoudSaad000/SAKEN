<?php

use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {

    // Authenticated User Routes
    Route::post('logout', [UserController::class, 'logout']);
    Route::get('user', [UserController::class, 'GetUser']);
    Route::delete('user/delete-account', [UserController::class, 'deleteMyAccount']);

    /*
    |--------------------------------------------------------------------------
    | User Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('users')->group(function () {

        // User self or admin update
        Route::post('{id}', [UserController::class, 'update']);

        // Admin-only User Routes
        Route::middleware('isAdmin')->group(function () {
            Route::put('{id}/approve', [UserController::class, 'approveUser']);
            Route::put('{id}/reject', [UserController::class, 'rejectUser']);

            Route::put('approveAll', [UserController::class, 'approveAllUsers']);
            Route::put('rejectAll', [UserController::class, 'rejectAllUsers']);

            Route::get('isfalse', [UserController::class, 'getAllUsersis_approved_false']);
            Route::get('istrue', [UserController::class, 'getAllUsersis_approved_true']);

            Route::get('/', [UserController::class, 'getAllUsers']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Booking Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('bookings')->group(function () {

        // Renter Routes
        Route::middleware('isRenter')->group(function () {
            Route::apiResource('', BookingController::class)->parameters(['' => 'booking']);
            Route::put('{booking}/rate', [BookingController::class, 'rate']);
            Route::put('{booking}/pay', [BookingController::class, 'pay']);
        });

        // Admin Routes
        Route::middleware('isAdmin')->group(function () {
            Route::get('all', [BookingController::class, 'getAllBookings']);
        });

        // Owner Routes
        Route::middleware('isOwner')->group(function () {
            Route::get('{apartment}/unconfirmed', [BookingController::class, 'getUnConfirmedBookings']);
            Route::put('{booking}/confirm', [BookingController::class, 'confirmBooking']);
            Route::put('{booking}/reject', [BookingController::class, 'rejectBooking']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Apartment Management
    |--------------------------------------------------------------------------
    */
    Route::prefix('apartment')->group(function () {
        Route::apiResource('', ApartmentController::class);
    });

});
