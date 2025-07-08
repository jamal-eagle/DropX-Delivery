<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Illuminate\Http\Request;

class PromoCodeController extends Controller
{
    public function store(Request $request)
    {

        $request->validate([
            'code' => 'required|string|unique:promo_codes,code',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_uses' => 'required|integer|min:1',
            'expiry_date' => 'required|date|after:now',
        ]);


        $promo = PromoCode::create($request->only([
            'code',
            'discount_type',
            'discount_value',
            'min_order_value',
            'max_uses',
            'expiry_date',
        ]));

        return response()->json([
            'status' => true,
            'message' => 'تم إنشاء كود الخصم بنجاح',
            'data' => $promo,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $promo = PromoCode::findOrFail($id);

        $request->validate([
            'code' => 'sometimes|string|unique:promo_codes,code,' . $promo->id,
            'discount_type' => 'sometimes|in:percentage,fixed',
            'discount_value' => 'sometimes|numeric|min:0',
            'min_order_value' => 'sometimes|nullable|numeric|min:0',
            'max_uses' => 'sometimes|integer|min:1',
            'expiry_date' => 'sometimes|date|after:now',
        ]);

        $promo->update($request->only([
            'code',
            'discount_type',
            'discount_value',
            'min_order_value',
            'max_uses',
            'expiry_date',
        ]));

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث كود الخصم بنجاح',
            'data' => $promo,
        ], 201);
    }

    public function destroy($id)
    {
        $promo = PromoCode::findOrFail($id);
        $promo->delete();

        return response()->json([
            'status' => true,
            'message' => 'تم حذف كود الخصم بنجاح',
        ], 204);
    }

    public function index()
    {
        $promos = PromoCode::get();

        return response()->json([
            'status' => true,
            'data' => $promos,
        ], 200);
    }

    public function toggleActivation($id)
    {
        $promo = PromoCode::findOrFail($id);
        $promo->is_active = !$promo->is_active;
        $promo->save();

        return response()->json([
            'status' => true,
            'message' => 'تم تغيير حالة التفعيل',
            'is_active' => $promo->is_active,
        ], 200);
    }
}
