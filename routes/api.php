<?php

use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\auth\AuthController;
use App\Http\Controllers\meal\MealController;
use App\Http\Controllers\meal\SearchController;
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



Route::post('/register', [AuthController::class, 'register']);
Route::get('/get-Area',[AuthController::class,'index']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::prefix('user')->middleware(['auth:sanctum',\App\Http\Middleware\CheckCustomer::class])->group(function () {
    Route::get('/all-ads',[AdvertisementController::class,'get_all_ads']);
    Route::get('searchByResturant',[SearchController::class,'searchByNameResturant']);
    Route::get('desplayResturantWithmeals',[SearchController::class,'getRestaurantsInMyAreaWithMeals']);
    Route::get('searchMealByName',[SearchController::class,'searchMealByName']);
});




Route::prefix('admin')->middleware(['auth:sanctum','checkUserType'])->group(function () {
    Route::post('/store-meal', [MealController::class, 'storeMeal']);
    Route::post('/store-ads',[AdvertisementController::class,'storeAds']);
    Route::post('/update-ads/{id}',[AdvertisementController::class,'update_Ads']);
});




