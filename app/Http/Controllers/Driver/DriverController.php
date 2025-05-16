<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\DriverAreaTurn;
use App\Models\DriverOrderRejection;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverController extends Controller
{

    public function availableOrders()
    {
        $user = Auth::user();

        if ($user->user_type !== 'driver') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $driverId = $user->driver->id;

        $cityNames = $user->areas()->pluck('city')->unique();

        $rejectedOrderIds = DriverOrderRejection::where('driver_id', $driverId)->pluck('order_id')->toArray();

        $orders = Order::where('status', 'preparing')
            ->where('is_accepted', true)
            ->whereNull('driver_id')
            ->whereNotIn('id', $rejectedOrderIds)
            ->whereHas('user.areas', function ($query) use ($cityNames) {
                $query->whereIn('city', $cityNames);
            })
            ->with(['user', 'restaurant'])
            ->get();

        return response()->json([
            'orders' => $orders
        ]);
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
                'user',                // الزبون
                'restaurant',         // المطعم
                'orderItems.meal'          // الوجبات داخل الطلب
            ])
            ->orderByDesc('updated_at')
            ->get();

        return response()->json([
            'orders' => $orders
        ]);
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
                'user',                  // صاحب الطلب
                'restaurant',           // المطعم
                'orderItems.meal'       // تفاصيل كل وجبة داخل الطلب
            ])
            ->orderByDesc('updated_at')
            ->get();

        return response()->json([
            'orders' => $orders
        ]);
    }

    public function acceptOrder($order_id)
    {


        $user = Auth::user();

        if ($user->user_type !== 'driver') {
            return response()->json(['message' => 'غير مصرح للسائق'], 403);
        }

        $driver = $user->driver;

        // جلب الطلب والتحقق من أنه متاح للقبول
        $order = Order::where('id', $order_id)
            ->where('status', 'preparing')
            ->where('is_accepted', true)
            ->whereNull('driver_id')
            ->first();

        if (! $order) {
            return response()->json(['message' => 'الطلب غير متاح للقبول أو تم قبوله مسبقًا'], 404);
        }

        // جلب أسماء المدن التي يعمل فيها السائق
        $cityNames = $user->areas()->pluck('city')->unique()->toArray();

        // التأكد أن السائق لديه الدور في نفس المدينة
        $hasTurn = DriverAreaTurn::where('driver_id', $driver->id)
            ->whereHas('area', function ($query) use ($cityNames) {
                $query->whereIn('city', $cityNames);
            })
            ->where('is_next', true)
            ->where('is_active', true)
            ->first();

        if (! $hasTurn) {
            return response()->json(['message' => 'ليس لديك الدور حالياً ولا يمكنك قبول الطلب'], 403);
        }

        // ✅ تعيين السائق للطلب (بدون تغيير الحالة)
        $order->driver_id = $driver->id;
        $order->save();

        // ❗ سحب الدور من السائق الحالي
        $hasTurn->update([
            'is_next' => false,
            'turn_assigned_at' => null,
        ]);

        // 🔄 تدوير الدور إلى السائق التالي في نفس المنطقة
        $nextTurn = DriverAreaTurn::where('area_id', $hasTurn->area_id)
            ->where('is_active', true)
            ->where('turn_order', '>', $hasTurn->turn_order)
            ->orderBy('turn_order')
            ->first();

        // إذا لم يوجد سائق بعده، نرجع لأول سائق (دائري)
        if (! $nextTurn) {
            $nextTurn = DriverAreaTurn::where('area_id', $hasTurn->area_id)
                ->where('is_active', true)
                ->orderBy('turn_order')
                ->first();
        }

        // التأكد أن السائق التالي ليس عنده طلب on_delivery
        if ($nextTurn) {
            $hasActiveOrder = Order::where('driver_id', $nextTurn->driver_id)
                ->where('status', 'on_delivery')
                ->exists();

            if (! $hasActiveOrder) {
                $nextTurn->update([
                    'is_next' => true,
                    'turn_assigned_at' => now(),
                ]);
            }
        }

        return response()->json([
            'message' => '✅ تم تعيينك كسائق لهذا الطلب بنجاح.',
            'order_id' => $order->id,
        ]);
    }

    public function rejectOrder($order_id)
    {


        $user = Auth::user();

        if ($user->user_type !== 'driver') {
            return response()->json(['message' => 'غير مصرح للسائق'], 403);
        }

        $driver = $user->driver;

        // ✅ جلب الطلب والتحقق من حالته
        $order = Order::where('id', $order_id)
            ->where('status', 'preparing')
            ->where('is_accepted', true)
            ->whereNull('driver_id')
            ->first();

        if (! $order) {
            return response()->json(['message' => 'الطلب غير متاح للرفض أو تم تعيينه مسبقًا'], 404);
        }

        // ✅ جلب المدن التي يعمل بها السائق
        $cityNames = $user->areas()->pluck('city')->unique()->toArray();

        // ✅ التحقق من أن السائق هو صاحب الدور في نفس المدينة
        $currentTurn = DriverAreaTurn::where('driver_id', $driver->id)
            ->whereHas('area', function ($q) use ($cityNames) {
                $q->whereIn('city', $cityNames);
            })
            ->where('is_next', true)
            ->where('is_active', true)
            ->first();

        if (! $currentTurn) {
            return response()->json(['message' => 'ليس لديك الدور حالياً ولا يمكنك رفض الطلب'], 403);
        }

        // ✅ تسجيل الرفض في جدول driver_order_rejections
        DriverOrderRejection::create([
            'driver_id' => $driver->id,
            'order_id' => $order->id,
        ]);

        // ✅ سحب الدور من السائق الحالي
        $currentTurn->update([
            'is_next' => false,
            'turn_assigned_at' => null,
        ]);

        // ✅ تمرير الدور لسائق آخر (غير مشغول)
        $nextTurn = DriverAreaTurn::where('area_id', $currentTurn->area_id)
            ->where('is_active', true)
            ->where('turn_order', '>', $currentTurn->turn_order)
            ->orderBy('turn_order')
            ->first();

        if (! $nextTurn) {
            $nextTurn = DriverAreaTurn::where('area_id', $currentTurn->area_id)
                ->where('is_active', true)
                ->orderBy('turn_order')
                ->first();
        }

        if ($nextTurn) {
            $hasActiveOrder = Order::where('driver_id', $nextTurn->driver_id)
                ->where('status', 'on_delivery')
                ->exists();

            if (! $hasActiveOrder) {
                $nextTurn->update([
                    'is_next' => true,
                    'turn_assigned_at' => now(),
                ]);
            }
        }

        return response()->json([
            'message' => '✅ تم رفض الطلب وتم تمرير الدور.',
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

        return response()->json([
            'order' => $order
        ]);
    }
}
