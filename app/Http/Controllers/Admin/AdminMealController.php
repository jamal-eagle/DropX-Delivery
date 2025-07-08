<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ImageForMeal;
use App\Models\Meal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

    public function updateMeal(Request $request, $mealId)
    {
        $meal = Meal::findOrFail($mealId);

        $request->validate([
            'name' => 'nullable|string|max:100',
            'original_price' => 'nullable|numeric|min:0',
            'category_name' => 'nullable|string|max:100',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,jpeg,png|max:2048',
        ]);
        // response()->json($request);

        DB::beginTransaction();
        try {
            if ($request->has('name')) {
                $meal->name = $request->name;
            }

            if ($request->has('original_price')) {
                $meal->original_price = $request->original_price;
            }

            if ($request->has('category_name')) {
                $category = Category::firstOrCreate(['name' => $request->category_name]);
                $meal->category_id = $category->id;
            }

            $meal->save();

            if ($request->hasFile('images')) {
            foreach ($meal->images as $image) {
                if (Storage::disk('public')->exists($image->image)) {
                    Storage::disk('public')->delete($image->image);
                }
                $image->delete();
            }

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
                'message' => '✅ تم تعديل الوجبة بنجاح',
                'meal' => $meal,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => '❌ حدث خطأ أثناء تعديل الوجبة',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteMeal($mealId)
    {
        $meal = Meal::with('images')->findOrFail($mealId);

        DB::beginTransaction();
        try {
            foreach ($meal->images as $image) {
                if (Storage::disk('public')->exists($image->image)) {
                    Storage::disk('public')->delete($image->image);
                }
                $image->delete();
            }

            $meal->delete();

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => '✅ تم حذف الوجبة بنجاح',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => '❌ حدث خطأ أثناء الحذف',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
