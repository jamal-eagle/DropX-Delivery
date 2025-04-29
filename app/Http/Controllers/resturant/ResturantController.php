<?php

namespace App\Http\Controllers\resturant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ResturantController extends Controller
{
    public function getPreparingOrders()
    {
        $restaurantId = auth()->user()->restaurant->id;

        $orders = Cache::remember("preparing_orders_restaurant_{$restaurantId}", now()->addMinutes(15), function () use ($restaurantId) {
            return Order::with(['user', 'orderItems.meal.images'])
                ->where('restaurant_id', $restaurantId)
                ->where('status', 'preparing')
                ->latest()
                ->get();
        });

        return response()->json([
            'status' => true,
            'orders' => $orders
        ], 200);
    }
    public function getPendingOrders()
    {
        $restaurantId = auth()->user()->restaurant->id;

        $orders = Cache::remember("pending_orders_restaurant_{$restaurantId}", now()->addMinutes(15), function () use ($restaurantId) {
            return Order::with(['user', 'orderItems.meal.images'])
                ->where('restaurant_id', $restaurantId)
                ->where('status', 'pending')
                ->latest()
                ->get();
        });

        return response()->json([
            'status' => true,
            'orders' => $orders
        ], 200);
    }

    public function acceptOrder($orderId)
    {
        $restaurant = auth()->user()->restaurant;

        $order = Order::where('id', $orderId)
            ->where('restaurant_id', $restaurant->id)
            ->where('status', 'pending')
            ->first();

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'الطلب غير موجود أو تم قبوله مسبقًا.',
            ], 404);
        }

        $order->status = 'preparing';
        $order->is_accepted = true;
        $order->save();

        Cache::forget("pending_orders_restaurant_{$restaurant->id}");
        Cache::forget("preparing_orders_restaurant_{$restaurant->id}");

        return response()->json([
            'status' => true,
            'message' => 'تم قبول الطلب بنجاح',
            'order' => $order,
        ], 200);
    }

    public function rejectOrder($orderId)
    {
        $restaurant = auth()->user()->restaurant;

        $order = Order::where('id', $orderId)
            ->where('restaurant_id', $restaurant->id)
            ->where('status', 'pending')
            ->first();

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'الطلب غير موجود .',
            ], 404);
        }
        $order->status = 'rejected';
        $order->is_accepted = false;
        $order->save();

        Cache::forget("pending_orders_restaurant_{$restaurant->id}");

        return response()->json([
            'status' => true,
            'message' => 'تم رفض الطلب بنجاح',
        ], 200);
    }
}
