<?php

namespace App\Http\Controllers\meal;

use App\Http\Controllers\Controller;
use App\Http\Requests\MealRequest;
use App\Models\Meal;
use Illuminate\Http\Request;

class MealController extends Controller
{
public function storeMeal(MealRequest $request)
    {
        $meal = Meal::create([
            'category_id' => $request->category_id,
            'name' => $request->name,
            'original_price' => $request->original_price,
        ]);


        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('meals', 'public');

                $description = $request->descriptions[$index] ?? null;

                $meal->images()->create([
                    'image' => $path,
                    'description' => $description,
                ]);
            }
        }
        return response()->json([
            'status' => true,
            'message' => 'تمت إضافة الوجبة بنجاح',
            'meal' => $meal->load('images')
        ]);
    }
}
