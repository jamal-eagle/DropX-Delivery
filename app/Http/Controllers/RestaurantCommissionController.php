<?php

namespace App\Http\Controllers;

use App\Models\RestaurantCommission;
use Illuminate\Http\Request;

class RestaurantCommissionController extends Controller
{
    public function updateCommission(Request $request ,$resturant_id)
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
}
