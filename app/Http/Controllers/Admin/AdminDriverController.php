<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Driver;
use App\Models\DriverAreaTurn;
use App\Models\DriverDailyReport;
use App\Models\DriverMonthlyReport;
use App\Models\DriverWorkingHour;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminDriverController extends Controller
{
    public function storeDriver(Request $request)
    {
        $request->validate([
            'fullname' => 'required|string|max:75',
            'phone' => 'required|string|max:15|unique:users,phone',
            'password' => 'required|string|min:6',
            'vehicle_type' => 'required|string|max:50',
            'vehicle_number' => 'required|string|max:50',
            'city' => 'required|string|max:100',
            'working_hours' => 'required|array|min:1',
            'working_hours.*.day_of_week' => 'required|string|in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
            'working_hours.*.start_time' => 'required|date_format:H:i',
            'working_hours.*.end_time' => 'required|date_format:H:i|after:working_hours.*.start_time',
        ]);

        DB::beginTransaction();

        try {
            $area = Area::firstOrCreate(['city' => $request->city]);

            $user = User::create([
                'fullname' => $request->fullname,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'user_type' => 'driver',
                'is_active' => true,
                'is_verified' => true,
            ]);

            $driver = Driver::create([
                'user_id' => $user->id,
                'vehicle_type' => $request->vehicle_type,
                'vehicle_number' => $request->vehicle_number,
                'is_active' => true,
            ]);

            DB::table('area_user')->insert([
                'user_id' => $user->id,
                'area_id' => $area->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $maxOrder = DriverAreaTurn::where('area_id', $area->id)->max('turn_order') ?? 0;

            DriverAreaTurn::create([
                'driver_id' => $driver->id,
                'area_id' => $area->id,
                'turn_order' => $maxOrder + 1,
                'is_next' => $maxOrder === 0,
                'is_active' => true,
            ]);

            foreach ($request->working_hours as $wh) {
                DriverWorkingHour::create([
                    'driver_id' => $driver->id,
                    'day_of_week' => $wh['day_of_week'],
                    'start_time' => $wh['start_time'],
                    'end_time' => $wh['end_time'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => '✅ تم إنشاء السائق بنجاح.',
                'user' => $user,
                'driver' => $driver,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '❌ حدث خطأ أثناء إنشاء السائق.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function resetDriverPassword(Request $request)
    {
        $request->validate([
            'driver_user_id' => 'required|exists:users,id',
            'new_password' => 'required|string|min:6'
        ]);

        $user = User::find($request->driver_user_id);
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'تم تغيير كلمة المرور بنجاح'], 200);
    }


    public function indexDrivers()
    {
        $drivers = User::with('driver')
            ->where('user_type', 'driver')
            ->where('is_active', true)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'قائمة جميع السائقين',
            'data' => $drivers,
        ], 200);
    }

    public function getDriversByCity($city)
    {
        $drivers = Driver::whereHas('user.areas', function ($q) use ($city) {
            $q->whereRaw('LOWER(TRIM(city)) = ?', [$city]);
        })
            ->with(['user:id,fullname,phone'])
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'قائمة السائقين في المدينة المحددة',
            'data' => $drivers,
        ]);
    }

    public function getActiveWorkingDrivers()
    {
        $now = now();
        $day = $now->format('l');

        $workingDrivers = DriverAreaTurn::where('is_active', true)
            ->whereHas('driver.user', function ($q) {
                $q->where('is_active', true);
            })
            ->whereHas('driver.workingHours', function ($q) use ($day, $now) {
                $q->where('day_of_week', $day)
                    ->whereTime('start_time', '<=', $now->format('H:i:s'))
                    ->whereTime('end_time', '>=', $now->format('H:i:s'));
            })
            ->with([
                'driver.user:id,fullname,phone'
            ])
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'السائقون المتاحون حالياً',
            'data' => $workingDrivers,
        ]);
    }
    public function getActiveWorkingDriversByCity($city)
    {
        $now = now();
        $day = $now->format('l');

        if (!$city) {
            return response()->json([
                'status' => false,
                'message' => 'يرجى تحديد المدينة'
            ], 422);
        }

        $workingDrivers = DriverAreaTurn::where('is_active', true)
            ->whereHas('driver.user', function ($q) {
                $q->where('is_active', true);
            })
            ->whereHas('driver.workingHours', function ($q) use ($day, $now) {
                $q->where('day_of_week', $day)
                    ->whereTime('start_time', '<=', $now->format('H:i:s'))
                    ->whereTime('end_time', '>=', $now->format('H:i:s'));
            })
            ->whereHas('driver.user.areas', function ($q) use ($city) {
                $q->whereRaw('LOWER(TRIM(city)) = ?', [$city]);
            })
            ->with([
                'driver.user:id,fullname,phone'
            ])
            ->get();

        return response()->json([
            'status' => true,
            'message' => "السائقون المتاحون حالياً في المدينة: {$city}",
            'data' => $workingDrivers,
        ]);
    }

    public function getCurrentDriverInTurnByCity($city)
    {

        if (!$city) {
            return response()->json([
                'status' => false,
                'message' => 'يرجى تحديد المدينة'
            ], 422);
        }

        $currentTurn = DriverAreaTurn::where('is_active', true)
            ->where('is_next', true)
            ->whereHas('driver.user.areas', function ($query) use ($city) {
                $query->whereRaw('LOWER(TRIM(city)) = ?', [$city]);
            })
            ->with(['driver.user:id,fullname,phone', 'driver.workingHours'])
            ->first();

        if (!$currentTurn) {
            return response()->json([
                'status' => false,
                'message' => 'لا يوجد سائق لديه الدور حالياً في هذه المدينة'
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'السائق الحالي الذي لديه الدور',
            'data' => $currentTurn
        ]);
    }

    public function getInactiveButWorkingDrivers()
    {
        $now = now();
        $currentDay = $now->format('l'); // Sunday, Monday...
        $currentTime = $now->format('H:i:s');

        $drivers = DriverAreaTurn::where('is_active', false)
            ->whereHas('driver.workingHours', function ($q) use ($currentDay, $currentTime) {
                $q->where('day_of_week', $currentDay)
                    ->where('start_time', '<=', $currentTime)
                    ->where('end_time', '>=', $currentTime);
            })
            ->with(['driver.user:id,fullname,phone', 'driver.workingHours'])
            ->get();

        return response()->json([
            'status' => true,
            'message' => '🚨 السائقون الغير نشطين حالياً في جدول الدور ولكنهم ضمن وقت دوامهم',
            'data' => $drivers,
        ]);
    }

    public function getInactiveButWorkingDriversByCityName($cityName)
    {
        $now = \Carbon\Carbon::now();
        $currentDay = $now->format('l');
        $currentTime = $now->format('H:i:s');

        $drivers = DriverAreaTurn::where('is_active', false)
            ->whereHas('driver.workingHours', function ($q) use ($currentDay, $currentTime) {
                $q->where('day_of_week', $currentDay)
                    ->where('start_time', '<=', $currentTime)
                    ->where('end_time', '>=', $currentTime);
            })
            ->whereHas('area', function ($q) use ($cityName) {
                $q->whereRaw('LOWER(TRIM(city)) = ?', [strtolower(trim($cityName))]);
            })
            ->with(['driver.user:id,fullname,phone', 'driver.workingHours', 'area'])
            ->get();

        return response()->json([
            'status' => true,
            'message' => "السائقون المتسربون في مدينة {$cityName} والذين ضمن أوقات دوامهم",
            'data' => $drivers
        ]);
    }

    public function getTodayCompletedOrdersForDriver($driverId)
    {
        if (!$driverId) {
            return response()->json([
                'status' => false,
                'message' => 'يجب تحديد معرف السائق driver_id'
            ], 400);
        }

        $today = Carbon::today();

        $orders = Order::where('driver_id', $driverId)
            ->where('status', 'delivered')
            ->whereDate('created_at', $today)
            ->with(['user:id,fullname', 'restaurant.user:id,fullname'])
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'قائمة الطلبات المكتملة لهذا اليوم',
            'data' => $orders
        ]);
    }

    public function getTodayOnDeliveryOrdersForDriver($driverId)
    {
        if (!$driverId) {
            return response()->json([
                'status' => false,
                'message' => 'يجب تحديد معرف السائق driver_id'
            ], 400);
        }

        $today = Carbon::today();

        $orders = Order::where('driver_id', $driverId)
            ->where('status', 'on_delivery')
            ->whereDate('created_at', $today)
            ->with(['user:id,fullname', 'restaurant.user:id,fullname'])
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'قائمة الطلبات الجارية لهذا اليوم',
            'data' => $orders
        ]);
    }

    public function getTodayPendingOrdersForDriver($driverId)
    {
        if (!$driverId) {
            return response()->json([
                'status' => false,
                'message' => 'يجب تحديد معرف السائق driver_id'
            ], 400);
        }

        $today = Carbon::today();

        $orders = Order::where('driver_id', $driverId)
            ->where('status', 'pending')
            ->whereDate('created_at', $today)
            ->with(['user:id,fullname', 'restaurant.user:id,fullname'])
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'قائمة الطلبات التي لدى المطعم  لهذا اليوم',
            'data' => $orders
        ]);
    }


    public function getDriverDailyReport($driverId, $year, $month, $day)
    {
        $date = Carbon::createFromDate($year, $month, $day)->toDateString();

        $report = DriverDailyReport::where('driver_id', $driverId)
            ->where('date', $date)
            ->first();

        if (!$report) {
            return response()->json([
                'status' => false,
                'message' => "لا يوجد تقرير للسائق في هذا التاريخ",
            ]);
        }

        return response()->json([
            'status' => true,
            'driver_id' => $driverId,
            'date' => $date,
            'total_orders' => $report->total_orders,
            'total_amount' => $report->total_amount,
            'driver_earnings' => $report->driver_earnings,
        ]);
    }

    public function getDriverMonthlyReport($driverId, $year, $month)
    {
        $monthFormatted = sprintf('%04d-%02d', $year, $month); // مثل 2025-06

        $report = DriverMonthlyReport::where('driver_id', $driverId)
            ->where('month_date', $monthFormatted)
            ->first();

        if (!$report) {
            return response()->json([
                'status' => false,
                'message' => "لا يوجد تقرير شهري للسائق لهذا الشهر",
            ]);
        }

        return response()->json([
            'status' => true,
            'driver_id' => $driverId,
            'month_date' => $monthFormatted,
            'total_orders' => $report->total_orders,
            'total_amount' => $report->total_delivery_fees,
            'driver_earnings' => $report->driver_earnings,
        ]);
    }
}
