<?php

use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);


Route::middleware('auth:sanctum')->group(function(){

    // Admin-only route
    Route::get('users', [UserController::class, 'getAllUsers'])->middleware('isAdmin');
    
    // Bookings routes=============//
        
        // Renter routes
        Route::middleware('isRenter')->group(function () {
            Route::apiResource('/bookings', BookingController::class);
            Route::put('/bookings/{booking}/rate', [BookingController::class, 'rate']);
        });
        
        // Admin routes
        Route::middleware('isAdmin')->group(function () {
            Route::get('/bookings/all', [BookingController::class, 'getAllBookings']);
        });
        
        // Owner routes
        Route::middleware('isOwner')->group(function () {


 Route::apiResource('apartment',ApartmentController::class)->middleware('auth:sanctum');
 Route::get('apartments/filter', [ApartmentController::class, 'filter'])->middleware('auth:sanctum');
            Route::get('/bookings/{apartment_id}/unconfirmed', [BookingController::class, 'getUnConfirmedBookings']);
            Route::put('/bookings/{booking_id}/confirm', [BookingController::class, 'confirmBooking']);
            Route::put('/bookings/{booking_id}/reject', [BookingController::class, 'rejectBooking']);
        });
    // ===========================================// 
    
    Route::apiResource('apartment',ApartmentController::class);
});
