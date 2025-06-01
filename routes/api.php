<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\area\AreaController;
use App\Http\Controllers\auth\AuthController;
use App\Http\Controllers\Driver\DriverController;
use App\Http\Controllers\meal\MealController;
use App\Http\Controllers\meal\SearchController;
use App\Http\Controllers\order\OrderController;
use App\Http\Controllers\resturant\RestaurantCommissionController;
use App\Http\Controllers\resturant\ResturantController;
use App\Http\Middleware\CheckResturant;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
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


Route::post('/register', [AuthController::class, 'register']); //
Route::post('/verify-otp', [AuthController::class, 'verifyOTP']); //

Route::get('/get-Area', [AuthController::class, 'index']); //
Route::post('/login', [AuthController::class, 'login']); //
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum'); //

Route::prefix('user')->middleware(['auth:sanctum', \App\Http\Middleware\CheckCustomer::class])->group(function () {
    Route::get('infoUser', [AuthController::class, 'userInfo']); //
    Route::put('updateUserInfo', [AuthController::class, 'updateUserInfo']); //
    Route::get('/all-ads', [AdvertisementController::class, 'get_all_ads']); //
    Route::get('searchByNameResturant/{city}', [SearchController::class, 'searchByNameResturant']); //
    Route::get('getRestaurantsByCity/{city}', [SearchController::class, 'getRestaurantsByCity']); //
    Route::get('searchMealByName/{city}', [SearchController::class, 'searchMealByName']); //
    Route::post('addAddress', [AreaController::class, 'addAddress']); ///
    Route::get('/desplayMyAddresses', [AreaController::class, 'getMyAddresses']); ///
    Route::delete('/deleteAddresses/{areaId}', [AreaController::class, 'deleteAddress']); ///
    Route::post('addOrder', [OrderController::class, 'createOrderWithPromo']); //
    Route::post('/orders/{order_id}/apply-promo', [OrderController::class, 'applyPromoToOrder1']);
    Route::put('/updateOrders/{order_id}', [OrderController::class, 'updateOrder']);
    Route::put('updateOrAddMealToOrder/{orderId}', [OrderController::class, 'updateOrAddMealToOrder']);
    Route::delete('deleteMealsFromOrder/{orderId}', [OrderController::class, 'deleteOrderMeals']);
    Route::get('/getMyAllOrder', [OrderController::class, 'getMyOrders']); //
    Route::get('/getCompletedOrdersForUser', [OrderController::class, 'getCompletedOrdersForUser']); //
    Route::get('/getMealsByCity/{city}', [OrderController::class, 'getMealsByCity']); //
    Route::get('/getAllMealsOnMyAppletion', [OrderController::class, 'getAllMeals']); //
});

Route::prefix('resturant')->middleware(['auth:sanctum', CheckResturant::class])->group(function () {
    Route::get('/getPreparingOrders', [ResturantController::class, 'getPreparingOrders']);
    Route::get('/getNewOrders', [ResturantController::class, 'getPendingOrders']);
    Route::put('/acceptOrder/{orderId}', [ResturantController::class, 'acceptOrder']);
    Route::put('/rejectOrder/{orderId}', [ResturantController::class, 'rejectOrder']);
    Route::get('/getOrderDetails/{orderId}', [ResturantController::class, 'getOrderDetails']);
    Route::put('/updateWorkingHours', [ResturantController::class, 'updateWorkingHours']);
    Route::put('/updateResturantStatusClosed', [ResturantController::class, 'updateResturantStatusClose']);
    Route::put('/updateResturantStatusOpened', [ResturantController::class, 'updateResturantStatusOpen']);
    Route::get('/desplayMyMeals', [ResturantController::class, 'desplayMyMeals']);
    Route::put('/updateMealStatusWithPrice/{mealId}', [ResturantController::class, 'toggleMealAvailability']);
    Route::get('/getProfileResturant', [ResturantController::class, 'getResturantProfile']);
});

Route::prefix('driver')->middleware(['auth:sanctum', \App\Http\Middleware\CheckDriver::class])->group(function () {
    Route::get('desplayAvailableOrder/prepring', [DriverController::class, 'availableOrders']);
    Route::get('orders/completed', [DriverController::class, 'completedOrders']);
    Route::get('orders/Notcompleted', [DriverController::class, 'orderforrdivernotcomplete']);
    Route::put('orders/acceptOrder/{order_id}', [DriverController::class, 'acceptOrder']);
    Route::post('orders/rejectOrder/{order_id}', [DriverController::class, 'rejectOrder']);
    Route::get('orders/getOrderDetails/{order_id}', [DriverController::class, 'getOrderDetails']);
    Route::put('updateAvailabilityTofalse',[DriverController::class, 'updateAvailabilityToFalse']);
    Route::put('updateAvailabilityTotrue',[DriverController::class, 'updateAvailabilityToTrue']);
});

Route::prefix('admin')->middleware(['auth:sanctum', 'checkUserType'])->group(function () {
    Route::post('/meal/store-meal', [MealController::class, 'storeMeal']);
    Route::post('/ads/store-ads', [AdvertisementController::class, 'storeAds']);
    Route::post('/ads/update-ads/{id}', [AdvertisementController::class, 'update_Ads']);
    Route::post('/driver/storeDriver', [AdminController::class, 'storeDriver']);
    Route::put('/driver/resetDriverPassword', [AdminController::class, 'resetDriverPassword']);
    Route::put('/restaurant/commission', [RestaurantCommissionController::class, 'updateCommission']);
});
