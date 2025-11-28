<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\Welcomecontroller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/Welcome', [Welcomecontroller::class, 'Welcome']);

Route::get('/User', [UserController::class, 'index']);
Route::get('User/{id}', [UserController::class, 'CheckUser']);
