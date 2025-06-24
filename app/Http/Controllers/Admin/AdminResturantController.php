<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RestaurantCommission;
use App\Models\RestaurantDailyReport;
use App\Models\RestaurantMonthlyReport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminResturantController extends Controller
{
    public function storeRestaurant(Request $request)
    {
        $request->validate([
            'fullname'            => 'required|string|max:75',
            'phone'               => 'required|string|max:15|unique:users,phone',
            'password'            => 'required|string|min:6',
            'city'                => 'required|string|max:100',
            'description'         => 'nullable|string',
            'working_hours_start' => 'nullable|date_format:H:i',
            'working_hours_end'   => 'nullable|date_format:H:i|after:working_hours_start',
            'commission_type'     => 'required|in:percentage,fixed',
            'commission_value'    => 'required|numeric|min:0',
            'image'               => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $area = Area::firstOrCreate(['city' => $request->city]);

            $user = User::create([
                'fullname'    => $request->fullname,
                'phone'       => $request->phone,
                'password'    => Hash::make($request->password),
                'user_type'   => 'restaurant',
                'is_active'   => true,
                'is_verified' => true,
            ]);

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('restaurants', 'public');
            }

            $restaurant = Restaurant::create([
                'user_id'             => $user->id,
                'image'               => $imagePath,
                'description'         => $request->description,
                'working_hours_start' => $request->working_hours_start,
                'working_hours_end'   => $request->working_hours_end,
                'status'              => 'open',
            ]);

            RestaurantCommission::create([
                'restaurant_id' => $restaurant->id,
                'type'          => $request->commission_type,
                'value'         => $request->commission_value,
            ]);

            DB::table('area_user')->insert([
                'user_id'    => $user->id,
                'area_id'    => $area->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message'    => '✅ تم إنشاء حساب المطعم بنجاح.',
                'user'       => $user,
                'restaurant' => $restaurant,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => '❌ حدث خطأ أثناء إنشاء المطعم.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function resetRestaurantPassword(Request $request, $restaurantId)
    {
        $request->validate([
            'new_password' => 'required|string|min:6'
        ]);

        $restaurant = Restaurant::find($restaurantId);

        if (!$restaurant || !$restaurant->user_id) {
            return response()->json(['message' => 'لا يوجد مستخدم مرتبط بهذا المطعم'], 404);
        }

        $user = User::find($restaurant->user_id);

        if (!$user) {
            return response()->json(['message' => 'المستخدم غير موجود'], 404);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'تم تغيير كلمة المرور بنجاح'], 200);
    }

    public function updateRestaurant(Request $request, $restaurantId)
    {
        $request->validate([
            'fullname' => 'sometimes|string|max:75',
            'phone' => 'sometimes|string|max:15|unique:users,phone',
            'city' => 'sometimes|string|max:100',
            'description' => 'nullable|string',
            'commission_type' => 'sometimes|in:percentage,fixed',
            'commission_value' => 'sometimes|numeric|min:0',
            'image' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $restaurant = Restaurant::with('user')->findOrFail($restaurantId);
            $user = $restaurant->user;

            if ($request->has('fullname') && $request->fullname) {
                $user->fullname = $request->fullname;
            }

            if ($request->has('phone') && $request->phone) {
                $user->phone = $request->phone;
            }

            $user->save();

            if ($request->has('description')) {
                $restaurant->description = $request->description;
            }

            if ($request->has('image')) {
                $restaurant->image = $request->image;
            }

            $restaurant->save();

            if ($request->has('commission_type') && $request->has('commission_value')) {
                RestaurantCommission::updateOrCreate(
                    ['restaurant_id' => $restaurant->id],
                    [
                        'type' => $request->commission_type,
                        'value' => $request->commission_value,
                    ]
                );
            }

            if ($request->has('city') && $request->city) {
                $area = Area::firstOrCreate(['city' => $request->city]);

                DB::table('area_user')->where('user_id', $user->id)->delete();

                DB::table('area_user')->insert([
                    'user_id' => $user->id,
                    'area_id' => $area->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => '✅ تم تحديث بيانات المطعم بنجاح.',
                'restaurant' => $restaurant->fresh('user')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '❌ حدث خطأ أثناء تحديث بيانات المطعم.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getRestaurantOrdersByStatus(Request $request, $restaurantId)
    {
        $request->validate([
            'status' => 'required|in:pending,preparing,on_delivery,delivered,rejected',
        ]);

        $orders = Order::where('restaurant_id', $restaurantId)
            ->where('status', $request->status)
            ->with([
                'user:id,fullname,phone',
                'driver.user:id,fullname,phone'
            ])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => true,
            'message' => "📦 الطلبات التي حالتها {$request->status} للمطعم رقم {$restaurantId}",
            'data' => $orders,
        ]);
    }

    public function getAllRestaurants()
    {
        $restaurants = Restaurant::with([
            'user' => function ($q) {
                $q->select('id', 'fullname', 'phone')
                    ->with(['areas:id,city']);
            },
            'commission'
        ])->get();

        return response()->json([
            'status' => true,
            'message' => '📋 قائمة بجميع المطاعم',
            'data' => $restaurants
        ]);
    }

    public function getRestaurantsByCity(Request $request, $city)
    {

        $restaurants = Restaurant::select('restaurants.*', 'users.fullname', 'users.phone', 'areas.city')
            ->join('users', 'restaurants.user_id', '=', 'users.id')
            ->join('area_user', 'users.id', '=', 'area_user.user_id')
            ->join('areas', 'area_user.area_id', '=', 'areas.id')
            ->where('areas.city', $city)
            ->get();

        return response()->json([
            'status' => true,
            'message' => "📍 المطاعم الموجودة في مدينة {$request->city}",
            'data' => $restaurants,
        ]);
    }

    public function getRestaurantDetailsWithMeals($restaurantId)
    {
        $restaurant = Restaurant::with([
            'user:id,fullname,phone',
            'meals' => function ($query) {
                $query->select('id', 'restaurant_id', 'name', 'original_price');
            }
        ])->find($restaurantId);


        if (!$restaurant) {
            return response()->json([
                'status' => false,
                'message' => '❌ لم يتم العثور على المطعم.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => '✅ تفاصيل المطعم مع قائمة الوجبات.',
            'data' => $restaurant,
        ]);
    }

    public function getDeliveredOrdersByDayForresturant($restaurantId, $year, $month, $day)
    {
        $date = Carbon::createFromDate($year, $month, $day)->toDateString();

        $orders = Order::where('restaurant_id', $restaurantId)
            ->where('status', 'delivered')
            ->whereDate('created_at', $date)
            ->with(['user:id,fullname', 'driver:id,user_id', 'driver.user:id,fullname,phone'])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => true,
            'message' => "📦 الطلبات المكتملة للمطعم رقم {$restaurantId} في تاريخ {$date}",
            'data' => $orders,
        ]);
    }

    public function getDeliveredOrdersForRestaurantByMonth($restaurantId, $year, $month)
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $endDate = $startDate->copy()->endOfMonth()->endOfDay();

        $orders = Order::where('restaurant_id', $restaurantId)
            ->where('status', 'delivered')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['user:id,fullname', 'driver:id,user_id', 'driver.user:id,fullname,phone'])
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => true,
            'message' => "📦 الطلبات المكتملة للمطعم رقم {$restaurantId} في شهر {$year}-{$month}",
            'data' => $orders,
        ]);
    }

    public function getRestaurantDailyReport($restaurantId, $year, $month, $day)
    {
        $date = Carbon::createFromDate($year, $month, $day)->toDateString();

        $report = RestaurantDailyReport::where('restaurant_id', $restaurantId)
            ->where('date', $date)
            ->first();

        if (!$report) {
            return response()->json([
                'status' => false,
                'message' => "لا يوجد تقرير للمطعم في هذا التاريخ",
            ]);
        }

        return response()->json([
            'status' => true,
            'restaurant_id' => $restaurantId,
            'date' => $date,
            'total_orders' => $report->total_orders,
            'total_amount' => $report->total_amount,
            'restaurant_earnings' => $report->restaurant_earnings,
        ]);
    }

    public function getRestaurantMonthlyReport($restaurantId, $year, $month)
    {
        $monthString = sprintf('%04d-%02d', $year, $month);

        $report = RestaurantMonthlyReport::where('restaurant_id', $restaurantId)
            ->where('month_date', $monthString)
            ->first();

        if (!$report) {
            return response()->json([
                'status' => false,
                'message' => "لا يوجد تقرير للمطعم في هذا الشهر",
            ]);
        }

        return response()->json([
            'status' => true,
            'restaurant_id' => $restaurantId,
            'month_date' => $monthString,
            'total_orders' => $report->total_orders,
            'total_amount' => $report->total_amount,
            'restaurant_earnings' => $report->restaurant_earnings,
            'admin_earnings' => $report->admin_earnings,
        ]);
    }
}
