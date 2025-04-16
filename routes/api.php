<?php

use App\Http\Controllers\meal\MealController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::post('/register', [\App\Http\Controllers\auth\AuthController::class, 'register']);
Route::post('/login', [\App\Http\Controllers\auth\AuthController::class, 'login']);
Route::post('/logout', [\App\Http\Controllers\auth\AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::prefix('admin')->middleware(['auth:sanctum','checkUserType'])->group(function () {
    Route::post('/store-meal', [MealController::class, 'storeMeal']);
});




