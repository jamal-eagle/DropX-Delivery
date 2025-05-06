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

        $cityNames = Area::whereIn('id', function ($q) use ($user) {
            $q->select('area_id')->from('area_user')->where('user_id', $user->id);
        })->pluck('city')->unique();

        $rejectedOrderIds = DriverOrderRejection::where('driver_id', $driverId)->pluck('order_id')->toArray();

        $orders = Order::where('status', 'preparing')
            ->whereNull('driver_id')
            ->whereNotIn('id', $rejectedOrderIds)
            ->whereHas('user.areaUser.area', function ($query) use ($cityNames) {
                $query->whereIn('city', $cityNames);
            })
            ->with(['user', 'restaurant'])
            ->get();

        return response()->json([
            'orders' => $orders
        ]);
    }
}
