<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Area;
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
}
