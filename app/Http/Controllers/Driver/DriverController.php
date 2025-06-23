<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Driver;
use App\Models\DriverAreaTurn;
use App\Models\DriverOrderRejection;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DriverController extends Controller
{

    private function rotateDriverTurn($currentDriver): bool
    {
        $currentTurn = DriverAreaTurn::where('driver_id', $currentDriver->id)
            ->where('is_active', true)
            ->first();

        if (!$currentTurn) {
            Log::error("❌ لم يتم العثور على دور للسائق ID {$currentDriver->id}.");
            return false;
        }

        $areaId = $currentTurn->area_id;

        $allTurns = DriverAreaTurn::where('is_active', true)
            ->where('area_id', $areaId)
            ->with(['driver.user', 'driver.workingHours'])
            ->orderBy('turn_order')
            ->get();

        $eligibleTurns = $allTurns->filter(function ($turn) use ($currentDriver) {
            $driver = $turn->driver;

            return $driver
                && $driver->id !== $currentDriver->id
                && $driver->is_active
                && $this->isDriverInWorkingHours($driver);
        });

        if ($eligibleTurns->isEmpty()) {
            $currentTurn->update([
                'is_next' => true,
                'turn_assigned_at' => now(),
            ]);

            Log::info("🚫 لا يوجد سائق متاح في المنطقة ID {$areaId} لتدوير الدور.");
            return false;
        }

        $nextTurn = $eligibleTurns->first();

        $currentTurn->update([
            'is_next' => false,
            'turn_assigned_at' => null,
        ]);

        $nextTurn->update([
            'is_next' => true,
            'turn_assigned_at' => now(),
        ]);

        Log::info("✅ تم تدوير الدور من السائق ID {$currentDriver->id} إلى السائق ID {$nextTurn->driver_id} في المنطقة ID {$areaId}");

        return true;
    }


    protected function isDriverInWorkingHours($driver)
    {
        $today = now()->format('l'); // Saturday, Sunday...

        $workingSchedule = $driver->workingHours()
            ->where('day_of_week', $today)
            ->first();

        if (!$workingSchedule) {
            return false;
        }

        $now = now()->format('H:i:s');

        return $now >= $workingSchedule->start_time && $now <= $workingSchedule->end_time;
    }

    public function availableOrdersPreparing()
    {
        $user = Auth::user();

        if ($user->user_type !== 'driver') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $driver = $user->driver;
        $driverId = $user->driver->id;

        // if (!$driver->is_active) {
        //     return response()->json(['message' => ' حالة السائق غير متاح حالياً يرجى تعديل الحالة لرؤية الطلبات المتاحة.'], 403);
        // }

        $driverCities = $user->areas()->pluck('city')->unique()->values()->toArray();

        $orders = Order::whereIn('status', ['preparing'])
            ->where('driver_id', $driverId)
            ->where('is_accepted', true)
            ->whereHas('restaurant.user.areas', function ($query) use ($driverCities) {
                $query->whereIn('city', $driverCities);
            })
            ->with(['user', 'restaurant.user', 'orderItems.meal'])
            ->get();

        return response()->json([
            'orders' => $orders,
        ], 200);
    }

    public function availableOrdersOnDelivery()
    {
        $user = Auth::user();

        if ($user->user_type !== 'driver') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $driver = $user->driver;
        $driverId = $user->driver->id;

        // if (!$driver->is_active) {
        //     return response()->json(['message' => ' حالة السائق غير متاح حالياً يرجى تعديل الحالة لرؤية الطلبات المتاحة.'], 403);
        // }

        $driverCities = $user->areas()->pluck('city')->unique()->values()->toArray();

        $orders = Order::whereIn('status', ['on_delivery'])
            ->where('driver_id', $driverId)
            ->where('is_accepted', true)
            ->whereHas('restaurant.user.areas', function ($query) use ($driverCities) {
                $query->whereIn('city', $driverCities);
            })
            ->with(['user', 'restaurant.user', 'orderItems.meal'])
            ->get();

        return response()->json([
            'orders' => $orders,
        ], 200);
    }

    public function completedOrders()
    {
        $user = Auth::user();

        if ($user->user_type !== 'driver') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $driverId = $user->driver->id;

        $orders = Order::where('driver_id', $driverId)
            ->where('status', 'delivered')
            ->with([
                'user',
                'restaurant.user',
                'orderItems.meal'
            ])
            ->orderByDesc('updated_at')
            ->get();

        return response()->json([
            'orders' => $orders
        ], 200);
    }

    public function orderforrdivernotcomplete()
    {
        $user = Auth::user();

        if ($user->user_type !== 'driver') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $driverId = $user->driver->id;

        $orders = Order::where('driver_id', $driverId)
            ->whereIn('status', ['preparing', 'on_delivery'])
            ->with([
                'user',
                'restaurant',
                'orderItems.meal'
            ])
            ->orderByDesc('updated_at')
            ->get();

        return response()->json([
            'orders' => $orders
        ]);
    }

    public function getOrderDetails($order_id)
    {
        $user = Auth::user();

        $order = Order::where('id', $order_id)
            ->with([
                'user',
                'restaurant.user',
                'orderItems.meal'
            ])
            ->first();

        if (! $order) {
            return response()->json(['message' => 'الطلب غير موجود'], 404);
        }
        $mealsCount = $order->orderItems->sum('quantity');

        return response()->json([
            'mealcount' => $mealsCount,
            'order' => $order,
        ], 200);
    }

    public function updateAvailabilityToFalse()
    {
        try {
            return DB::transaction(function () {
                $driver = auth()->user()->driver;

                if (!$driver) {
                    return response()->json([
                        'status' => false,
                        'message' => 'السائق غير موجود.',
                    ], 404);
                }

                $hasActiveOrders = Order::where('driver_id', $driver->id)
                    ->whereIn('status', ['preparing', 'on_delivery'])
                    ->exists();

                if ($hasActiveOrders) {
                    return response()->json([
                        'status' => false,
                        'message' => 'لا يمكنك تغيير حالتك الآن لوجود طلبات غير مكتملة.',
                    ], 403);
                }

                $turn = $driver->areaTurns()->where('is_next', true)->first();

                if ($turn) {
                    $rotated = $this->rotateDriverTurn($driver);

                    if (!$rotated) {
                        return response()->json([
                            'status' => false,
                            'message' => 'لا يمكن تغيير حالتك الآن لأنه لا يوجد سائق آخر متاح لتسلم الدور.',
                        ], 422);
                    }

                    $turn->update([
                        'is_active' => false,
                        'is_next' => false,
                    ]);
                } else {
                    // لا يملك الدور → فقط تغيير الحالة
                    $driver->areaTurns()->update(['is_active' => false]);
                }

                return response()->json([
                    'status' => true,
                    'message' => 'تم تغيير حالتك إلى غير متاح.',
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('خطأ أثناء تعديل حالة السائق: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ غير متوقع أثناء تغيير الحالة.',
            ], 500);
        }
    }

    public function updateAvailabilityToTrue()
    {
        try {
            return DB::transaction(function () {
                $driver = auth()->user()->driver;

                if (!$driver) {
                    return response()->json([
                        'status' => false,
                        'message' => 'السائق غير موجود.',
                    ], 404);
                }

                // ✅ استخدام التابع الجاهز للتحقق من وقت الدوام
                if (!$this->isDriverInWorkingHours($driver)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'لا يمكنك التوفر الآن، لأنك خارج أوقات الدوام المحددة لك.',
                    ], 403);
                }

                $driver->areaTurns()->update([
                    'is_active' => true
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'تم تغيير حالتك إلى متاح.',
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('خطأ أثناء تعديل حالة السائق: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ غير متوقع أثناء تغيير الحالة.',
            ], 500);
        }
    }

    public function scanOrderByDriver($orderId)
    {
        $driver = Driver::where('user_id', auth()->id())->first();

        if (!$driver) {
            return response()->json(['message' => 'لم يتم العثور على سائق مرتبط بهذا المستخدم.'], 404);
        }

        $order = Order::find($orderId);

        if (!$order) {
            return response()->json(['message' => 'الطلب غير موجود.'], 404);
        }

        if ($order->driver_id !== $driver->id) {
            return response()->json(['message' => 'أنت لست السائق الموكل بهذا الطلب.'], 403);
        }

        if ($order->status !== 'preparing') {
            return response()->json(['message' => 'لا يمكن مسح الطلب في حالته الحالية.'], 400);
        }

        $order->update([
            'status' => 'on_delivery',
        ]);

        return response()->json(['message' => '✅ تم تحويل حالة الطلب إلى on_delivery.'], 200);
    }
}
