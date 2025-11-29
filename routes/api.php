<?php

use App\Http\Controllers\BookingsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Welcomecontroller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware('auth:sanctum')->group(function () {
    
    
Route::get('/Welcome', [Welcomecontroller::class, 'Welcome']);

Route::get('/User', [UserController::class, 'index']);
Route::get('User/{id}', [UserController::class, 'CheckUser']);


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

});


