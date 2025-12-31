<?php

use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/verify-otp', [UserController::class, 'verifyOtp']);
Route::post('/resend-otp', [UserController::class, 'resendOtp']);
Route::post('/forgot-Password', [UserController::class, 'forgotPassword']);
Route::post('/reset-Password', [UserController::class, 'resetPassword']);

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('logout', [UserController::class, 'logout']);
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

        Route::middleware('isAdmin')->group(function () {

            Route::get('/all', [BookingController::class, 'getAllBookings']);
        });

        Route::middleware('isOwner')->group(function () {

            Route::get('/{apartment_id}/unconfirmed', [BookingController::class , 'getUnConfirmedBookings']);
            Route::get('/unconfirmed/all',[BookingController::class , 'getAllUnconfirmedBookings']);
            Route::put('/{booking_id}/confirm', [BookingController::class, 'confirmBooking']);
            Route::put('{booking}/reject', [BookingController::class, 'rejectBooking']);
        });
    });

    
    Route::prefix('apartment')->group(function () {
        Route::get('{apartmentId}/getCurrentBookingCheckoutDate', [ApartmentController::class , 'getCurrentBookingCheckoutDate']);
        Route::get('all', [ApartmentController::class, 'getAllApartments']);
        Route::get('filterGovernorate/{governorateId}', [ApartmentController::class, 'filterByGovernorate']);
        Route::get('filterCity/{cityId}', [ApartmentController::class, 'filterByCity']);
        Route::get('filterRooms/{numRooms}', [ApartmentController::class, 'filterByRooms']);
        Route::get('filterPrice/{minPrice?}/{maxPrice?}', [ApartmentController::class, 'filterByPrice']);
        Route::get('filterِArea/{minArea?}/{maxArea?}', [ApartmentController::class, 'filterByArea']);
        Route::post('{apartmentId}/favorite', [ApartmentController::class, 'addToFavorites']);
        Route::get('favorites', [ApartmentController::class, 'getFavorites']);
        Route::delete('{apartmentId}/removeFavorite', [ApartmentController::class, 'removeFromFavorites']);
    });
    Route::apiResource('apartment', ApartmentController::class)->middleware('isOwner');  
});
