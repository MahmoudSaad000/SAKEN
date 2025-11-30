<?php

use App\Http\Controllers\BookingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Welcomecontroller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');





Route::post('register', [UserController::class, 'register']);
Route::post('login',[UserController::class,'login']);
Route::post('logout',[UserController::class,'logout'])->middleware('auth:sanctum');



Route::middleware('auth:sanctum')->group(function () {

Route::prefix('/bookings')->group(function(){

    Route::middleware('isRenter')->group(function(){

        Route::apiResource('',BookingsController::class);
        Route::put('/{booking_id}/rate',[BookingsController::class,'rateBooking'])->middleware('isRenter');

    });

    Route::middleware('isAdmin')->group(function () {

        Route::get('/all',[BookingsController::class,'getAllBookings'])->middleware('isAdmin');

    });

    Route::middleware('isOwner')->group(function () {

        Route::get('/unconfirmed',[BookingsController::class,'getUnConfirmedBookings']);
        Route::put('/{booking_id}/confirm',[BookingsController::class,'confirmBooking']);

    });

});
Route::get('users', [UserController::class, 'getAllUsers'])->middleware('isAdmin');
});



