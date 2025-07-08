<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderAdminController extends Controller
{
    public function desplayAllOrdars()
    {
        if (auth()->user()->user_type !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'غير مصرح لك بعرض الطلبات.'
            ], 403);
        }

        $orders = Order::select('id', 'user_id', 'restaurant_id', 'driver_id', 'status', 'total_price', 'created_at')
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'تم جلب الطلبات بنجاح',
            'data' => $orders
        ], 200);
    }

    public function DesplayDetailsForOrder($id)
    {
        if (auth()->user()->user_type !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'غير مصرح لك بعرض تفاصيل الطلب.'
            ], 403);
        }

        $order = Order::with([
            'user:id,fullname,phone',
            'restaurant:id,user_id,image',
            'driver.user:id,fullname,phone',
            'orderItems.meal:id,name,original_price'
        ])->findOrFail($id);

        return response()->json([
            'status' => true,
            'message' => 'تفاصيل الطلب',
            'data' => $order
        ], 200);
    }
}
