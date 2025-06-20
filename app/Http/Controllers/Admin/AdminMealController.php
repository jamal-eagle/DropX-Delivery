<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ImageForMeal;
use App\Models\Meal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminMealController extends Controller
{
    public function storeMeal(Request $request, $restaurantId)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'original_price' => 'required|numeric|min:0',
            'category_name' => 'required|string|max:100',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png|max:2048'
        ]);

        return response()->json($request);

        DB::beginTransaction();
        try {
            $category = Category::firstOrCreate(['name' => $request->category_name]);

            $meal = Meal::create([
                'restaurant_id' => $restaurantId,
                'category_id' => $category->id,
                'name' => $request->name,
                'original_price' => $request->original_price,
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $imageFile) {
                    $path = $imageFile->store('meals', 'public');
                    ImageForMeal::create([
                        'meal_id' => $meal->id,
                        'image' => $path,
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => '✅ تم إضافة الوجبة بنجاح',
                'meal' => $meal,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => '❌ حدث خطأ أثناء الإضافة',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
