<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminDriverController;
use App\Http\Controllers\admin\AdminFeeController;
use App\Http\Controllers\admin\AdminMealController;
use App\Http\Controllers\admin\AdminResturantController;
use App\Http\Controllers\Admin\AuthAdminController;
use App\Http\Controllers\Admin\OrderAdminController;
use App\Http\Controllers\Admin\PromoCodeController;
use App\Http\Controllers\Admin\RestaurantCommissionController as AdminRestaurantCommissionController;
use App\Http\Controllers\AdvertisementController;
use App\Http\Controllers\area\AreaController;
use App\Http\Controllers\auth\AuthController;
use App\Http\Controllers\Driver\DriverController;
use App\Http\Controllers\meal\MealController;
use App\Http\Controllers\meal\SearchController;
use App\Http\Controllers\order\OrderController;
use App\Http\Controllers\Admin\RestaurantCommissionController;
use App\Http\Controllers\resturant\ResturantController;
use App\Http\Middleware\CheckResturant;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;


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
    Route::put('scanOrderBarcodeByUser/{order_id}', [OrderController::class, 'scanOrderBarcodeByUser']);
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
    Route::put('/updateMealStatusWithPrice/{mealId}', [ResturantController::class, 'updateMealStatusAndPrice']);
    Route::get('/getProfileResturant', [ResturantController::class, 'getResturantProfile']);
});

Route::prefix('driver')->middleware(['auth:sanctum', \App\Http\Middleware\CheckDriver::class])->group(function () {
    Route::get('desplayAvailableOrder/prepring', [DriverController::class, 'availableOrdersPreparing']);
    Route::get('desplayAvailableOrder/ondelivery', [DriverController::class, 'availableOrdersOnDelivery']);
    Route::get('orders/completed', [DriverController::class, 'completedOrders']);
    Route::get('orders/Notcompleted', [DriverController::class, 'orderforrdivernotcomplete']);
    Route::put('orders/acceptOrder/{order_id}', [DriverController::class, 'acceptOrder']);
    Route::post('orders/rejectOrder/{order_id}', [DriverController::class, 'rejectOrder']);
    Route::get('orders/getOrderDetails/{order_id}', [DriverController::class, 'getOrderDetails']);
    Route::put('updateAvailabilityTofalse', [DriverController::class, 'updateAvailabilityToFalse']);
    Route::put('updateAvailabilityTotrue', [DriverController::class, 'updateAvailabilityToTrue']);
    Route::put('scanOrderBarcodeByDriver/{order_id}', [DriverController::class, 'scanOrderByDriver']);
    Route::get('myProfile', [DriverController::class, 'myProfile']);

});

Route::post('/adminlogin', [AuthAdminController::class, 'login']);

Route::prefix('admin')->middleware(['auth:sanctum', 'checkUserType'])->group(function () {
    Route::get('/getallcustomers', [AuthAdminController::class, 'getCustomers']);
    Route::post('/users/{userId}/updateuseractivation', [AuthAdminController::class, 'toggleActiveStatus']); //
    Route::get('/all-ads', [AdvertisementController::class, 'get_all_ads']);
    Route::post('/ads/store-ads', [AdvertisementController::class, 'storeAds']);
    Route::post('/ads/update-ads/{id}', [AdvertisementController::class, 'update_Ads']);

    Route::get('/getPromoCode', [PromoCodeController::class, 'index']);
    Route::post('/AddPromoCode', [PromoCodeController::class, 'store']);
    Route::post('/UpdatePromoCode/{id}', [PromoCodeController::class, 'update']);
    Route::delete('/DeletePromoCode/{id}', [PromoCodeController::class, 'destroy']);
    Route::post('/updatePromoCodeActivation/{id}', [PromoCodeController::class, 'toggleActivation']);

    Route::get('/AllOrders', [OrderAdminController::class, 'desplayAllOrdars']);
    Route::get('/orderDetails/{id}', [OrderAdminController::class, 'DesplayDetailsForOrder']);


    Route::post('/driver/storeDriver', [AdminDriverController::class, 'storeDriver']);
    Route::post('/driverandresturant/resetDriverOrResturantPassword/{driverId}', [AdminDriverController::class, 'resetDriverPassword']);
    Route::get('/driver/desplayalldriver', [AdminDriverController::class, 'indexDrivers']);
    Route::get('/driver/getDriversByCity/{city}', [AdminDriverController::class, 'getDriversByCity']);
    Route::get('/driver/desplayalldriverActive', [AdminDriverController::class, 'getActiveWorkingDrivers']);
    Route::get('/driver/getActiveWorkingDriversByCity/{city}', [AdminDriverController::class, 'getActiveWorkingDriversByCity']);
    Route::get('/driver/getCurrentDriverInTurn/{city}', [AdminDriverController::class, 'getCurrentDriverInTurnByCity']);
    Route::get('/driver/desplayInactiveButWorkingDrivers', [AdminDriverController::class, 'getInactiveButWorkingDrivers']);
    Route::get('/driver/getInactiveButWorkingDriversByCityName/{city}', [AdminDriverController::class, 'getInactiveButWorkingDriversByCityName']);
    Route::get('/driver/today-deleveryorders/{driverId}', [AdminDriverController::class, 'getTodayCompletedOrdersForDriver']);
    Route::get('/driver/today-ondeleveryorders/{driverId}', [AdminDriverController::class, 'getTodayOnDeliveryOrdersForDriver']);
    Route::get('/driver/today-pendingorders/{driverId}', [AdminDriverController::class, 'getTodayPendingOrdersForDriver']);
    Route::get('/driver/getDriverDailyReport/{driverId}/{year}/{month}/{day}', [AdminDriverController::class, 'getDriverDailyReport']);
    Route::get('/driver/getDriverMonthlyReport/{driverId}/{year}/{month}', [AdminDriverController::class, 'getDriverMonthlyReport']);

    Route::post('/resturant/storeresturant', [AdminResturantController::class, 'storeRestaurant']);
    Route::post('/driverandresturant/resetRestaurantPassword/{resturant_id}', [AdminResturantController::class, 'resetRestaurantPassword']);
    Route::post('/resturant/updateRestaurant/{restaurant_id}', [AdminResturantController::class, 'updateRestaurant']);
    Route::get('/resturant/getAllRestaurants', [AdminResturantController::class, 'getAllRestaurants']);
    Route::get('/resturant/getRestaurantsByCity/{city}', [AdminResturantController::class, 'getRestaurantsByCity']);
    Route::get('/resturant/getRestaurantDetailsWithMeals/{restaurant_id}', [AdminResturantController::class, 'getRestaurantDetailsWithMeals']);
    Route::get('/resturant/getDeliveredOrdersByDayForresturant/{resturant_id}/{year}/{month}/{day}', [AdminResturantController::class, 'getDeliveredOrdersByDayForresturant']);
    Route::get('/resturant/getDeliveredOrdersForRestaurantByMonth/{resturant_id}/{year}/{month}', [AdminResturantController::class, 'getDeliveredOrdersForRestaurantByMonth']);
    Route::get('/resturant/getRestaurantDailyReport/{resturant_id}/{year}/{month}/{day}', [AdminResturantController::class, 'getRestaurantDailyReport']);
    Route::get('/resturant/getRestaurantMonthlyReport/{resturant_id}/{year}/{month}', [AdminResturantController::class, 'getRestaurantMonthlyReport']);
    Route::get('/resturant/getRestaurantOrdersByStatus/{resturant_Id}', [AdminResturantController::class, 'getRestaurantOrdersByStatus']);
    Route::post('/restaurant/addcommission/{restaurant_id}', [RestaurantCommissionController::class, 'setRestaurantCommission']); //
    Route::post('/restaurant/updatecommission/{restaurant_id}', [RestaurantCommissionController::class, 'updateCommission']); //
    Route::post('/meal/AddMeal/{resturant_id}', [AdminMealController::class, 'storeMeal']);
    Route::post('/meal/updateMeal/{meal_id}', [AdminMealController::class, 'updateMeal']);
    Route::delete('/meal/deleteMeal/{meal_id}', [AdminMealController::class, 'deleteMeal']);

    Route::get('/fee/getadminDailyfeesfromdriver/{year}/{month}/{day}', [AdminFeeController::class, 'getAdminDailyEarnings']);
    Route::get('/fee/getadminMonthlyfeesfromdriver/{year}/{month}', [AdminFeeController::class, 'getAdminMonthlyEarnings']);
    Route::get('/fee/getAdminDailyEarningsFromRestaurants/{year}/{month}/{day}', [AdminFeeController::class, 'getAdminDailyEarningsFromRestaurants']);
    Route::get('/fee/getAdminMonthlyEarningsFromRestaurants/{year}/{month}', [AdminFeeController::class, 'getAdminMonthlyEarningsFromRestaurants']);


    Route::post('/restaurant/updateDeliverySettings', [RestaurantCommissionController::class, 'updateDeliverySettings']);
});
