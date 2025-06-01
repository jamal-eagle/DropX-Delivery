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
        $userAreas = $currentDriver->user->areas;

        if ($userAreas->isEmpty()) {
            Log::warning("السائق ID {$currentDriver->id} غير مرتبط بأي منطقة.");
            return false;
        }

        $driverCities = $userAreas->pluck('city')->map(fn($city) => strtolower(trim($city)))->unique()->toArray();

        $allTurns = DriverAreaTurn::where('is_active', true)
            ->whereHas('driver.user.areas', function ($query) use ($driverCities) {
                $query->whereIn(DB::raw('LOWER(TRIM(city))'), $driverCities);
            })
            ->with(['driver.user.areas', 'driver.workingHours'])
            ->orderBy('turn_order')
            ->get();

        $currentTurn = $allTurns->firstWhere('driver_id', $currentDriver->id);

        if (!$currentTurn) {
            Log::error("❌ لم يتم العثور على دور للسائق ID {$currentDriver->id} ضمن السائقين في المدن: " . implode(', ', $driverCities));
            return false;
        }

        // فلترة السائقين المؤهلين (غيره)
        $eligibleTurns = $allTurns->filter(function ($turn) use ($currentDriver) {
            $driver = $turn->driver;

            return $driver
                && $driver->id !== $currentDriver->id
                && $driver->is_active
                && $this->isDriverInWorkingHours($driver);
        });

        // لا يوجد بديل مؤهل → لا تغيير
        if ($eligibleTurns->isEmpty()) {
            $currentTurn->update([
                'is_next' => true,
                'turn_assigned_at' => now(),
            ]);

            Log::info("🚫 لا يمكن تدوير الدور: لا يوجد سائق آخر متاح.");
            return false;
        }

        // تدوير الدور فعليًا
        $nextTurn = $eligibleTurns->first();

        $currentTurn->update([
            'is_next' => false,
            'turn_assigned_at' => null,
        ]);

        $nextTurn->update([
            'is_next' => true,
            'turn_assigned_at' => now(),
        ]);

        Log::info("✅ تم تدوير الدور من السائق ID {$currentDriver->id} إلى السائق ID {$nextTurn->driver_id}");

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

    public function availableOrders()
    {
        $user = Auth::user();

        if ($user->user_type !== 'driver') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $driver = $user->driver;
        $driverId = $user->driver->id;

        if (!$driver->is_active) {
            return response()->json(['message' => ' حالة السائق غير متاح حالياً يرجى تعديل الحالة لرؤية الطلبات المتاحة.'], 403);
        }

        $driverCities = $user->areas()->pluck('city')->unique()->values()->toArray();

        $rejectedOrderIds = DriverOrderRejection::where('driver_id', $driverId)
            ->pluck('order_id')
            ->toArray();

        $orders = Order::where('status', 'preparing')
            ->where('is_accepted', true)
            ->whereNull('driver_id')
            ->whereNotIn('id', $rejectedOrderIds)
            ->whereHas('restaurant.user.areas', function ($query) use ($driverCities) {
                $query->whereIn('city', $driverCities);
            })
            ->with(['user', 'restaurant.user.areas', 'orderItems.meal'])
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
                'restaurant',
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
                'user',                  // صاحب الطلب
                'restaurant',           // المطعم
                'orderItems.meal'       // تفاصيل كل وجبة
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
}
