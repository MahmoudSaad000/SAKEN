<?php

use App\Http\Controllers\ApartmentController;
use App\Http\Controllers\BookingController;
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

        Route::apiResource('',BookingController::class);
        Route::put('/{booking_id}/rate',[BookingController::class,'rateBooking'])->middleware('isRenter');

    });

    Route::middleware('isAdmin')->group(function () {

        Route::get('/all',[BookingController::class,'getAllBookings'])->middleware('isAdmin');

    });

    Route::middleware('isOwner')->group(function () {

        Route::get('/unconfirmed',[BookingController::class,'getUnConfirmedBookings']);
        Route::put('/{booking_id}/confirm',[BookingController::class,'confirmBooking']);

    });

});
Route::get('users', [UserController::class, 'getAllUsers'])->middleware('isAdmin');

Route::prefix('/apartment')->group(function(){

        Route::apiResource('',ApartmentController::class);
       
    });

});




