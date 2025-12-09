<?php

use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('logout', [UserController::class, 'logout']);
    Route::get('user', [UserController::class, 'GetUser']);
    Route::delete('user/delete-account', [UserController::class, 'deleteMyAccount']);

    Route::prefix('/users')->group(function () {

        Route::post('/{id}', [UserController::class, 'update']);

        Route::middleware('isAdmin')->group(function () {

            Route::put('/{id}/approve', [UserController::class, 'approveUser']);
            Route::put('/{id}/reject', [UserController::class, 'rejectUser']);
            Route::put('/approveAll', [UserController::class, 'approveAllUsers']);
            Route::put('/rejectAll', [UserController::class, 'rejectAllUsers']);
            Route::get('/isfalse', [UserController::class, 'getAllUsersis_approved_false']);
            Route::get('/istrue', [UserController::class, 'getAllUsersis_approved_true']);
        });
    });

    Route::prefix('/bookings')->group(function () {

        Route::middleware('isRenter')->group(function () {

            Route::apiResource('', BookingController::class);
            Route::put('/{booking_id}/rate', [BookingController::class, 'rateBooking'])->middleware('isRenter');
        });

        Route::middleware('isAdmin')->group(function () {

            Route::get('/all', [BookingController::class, 'getAllBookings'])->middleware('isAdmin');
        });

        Route::middleware('isOwner')->group(function () {

            Route::get('/unconfirmed', [BookingController::class, 'getUnConfirmedBookings']);
            Route::put('/{booking_id}/confirm', [BookingController::class, 'confirmBooking']);
        });

        Route::prefix('/bookings')->group(function () {
            Route::middleware('isRenter')->group(function () {
                Route::apiResource('', BookingController::class);
                Route::put('{booking}/rate', [BookingController::class, 'rateBooking']);
            });

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

});
