<?php

namespace App\Http\Controllers\Admin;

use App\Models\RestaurantCommission;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Delivery_Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RestaurantCommissionController extends Controller
{
    public function setRestaurantCommission(Request $request, $restaurant_id)
    {
        if (auth()->user()->user_type !== 'admin') {
            return response()->json([
                'status'  => false,
                'message' => 'غير مصرح لك بتنفيذ هذا الإجراء.',
            ], 403);
        }

        $request->validate([
            'type'  => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
        ]);

        $commission = RestaurantCommission::updateOrCreate(
            ['restaurant_id' => $restaurant_id],
            [
                'type'  => $request->type,
                'value' => $request->value,
            ]
        );

        // 4) الاستجابة
        return response()->json([
            'status'  => true,
            'message' => '✅ تم تعيين النسبة للمطعم بنجاح.',
            'data'    => $commission,
        ], 200);
    }

    public function updateCommission(Request $request, $resturant_id)
    {
        $user = auth()->user();

        if ($user->user_type !== 'admin') {
            return response()->json(['message' => 'غير مصرح لك بتنفيذ هذا الإجراء'], 403);
        }

        $validated = $request->validate([
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
        ]);

        $commission = RestaurantCommission::where('restaurant_id', $resturant_id)->first();

        if (!$commission) {
            return response()->json(['message' => 'لا يوجد سجل عمولة لهذا المطعم'], 404);
        }

        $commission->update([
            'type' => $validated['type'],
            'value' => $validated['value'],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم تعديل العمولة بنجاح',
            'commission' => $commission,
        ]);
    }

    public function updateDeliverySettings(Request $request)
    {
        $request->validate([
            'price_per_km' => 'required|numeric|min:0',
            'minimum_delivery_fee' => 'required|numeric|min:0',
        ]);

        $settings = Delivery_Setting::first();

        if ($settings) {
            $settings->update([
                'price_per_km' => $request->price_per_km,
                'minimum_delivery_fee' => $request->minimum_delivery_fee,
            ]);
        } else {
            $settings = Delivery_Setting::create([
                'price_per_km' => $request->price_per_km,
                'minimum_delivery_fee' => $request->minimum_delivery_fee,
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'تم حفظ إعدادات التوصيل بنجاح.',
            'data' => $settings,
        ]);
    }
}
