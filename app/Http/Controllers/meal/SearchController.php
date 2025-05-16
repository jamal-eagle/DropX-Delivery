<?php

namespace App\Http\Controllers\meal;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Category;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function getRestaurantsByCity($city)
    {
        $restaurants = User::whereHas('areas', function ($query) use ($city) {
            $query->where('city', $city);
        })
            ->whereHas('restaurant')
            ->with([
                'restaurant.categories.meals.images',
            ])
            ->get();

        $formattedRestaurants = $restaurants->map(function ($user) {
            return [
                'user_info' => [
                    'name' => $user->fullname,
                    'phone' => $user->phone,
                ],
                'restaurant_info' => [
                    'description' => $user->restaurant->description,
                    'status' => $user->restaurant->status,
                ],
                'categories' => $user->restaurant->categories->map(function ($category) {
                    return [
                        'category_name' => $category->name,
                        'meals' => $category->meals->map(function ($meal) {
                            return [
                                'meal_id' => $meal->id,
                                'name' => $meal->name,
                                'price' => $meal->original_price,
                                'is_available' => $meal->is_available,
                                'images' => $meal->images->map(function ($img) {
                                    return asset('storage/' . $img->image);
                                }),
                            ];
                        }),
                    ];
                }),
            ];
        });

        return response()->json([
            'status' => true,
            'city' => $city,
            'restaurants' => $formattedRestaurants,
        ], 200);
    }



    public function searchByNameResturant(Request $request, $city)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string',
        ]);

        $userAreaIds = $user->areas->pluck('id');

        $restaurantUser = User::whereHas('areas', function ($query) use ($userAreaIds) {
            $query->whereIn('areas.id', $userAreaIds);
        })
            ->where('fullname', 'LIKE', '%' . $request->name . '%')
            ->whereHas('restaurant')
            ->with(['restaurant', 'restaurant.meals.images'])
            ->first();

        if (!$restaurantUser) {
            return response()->json([
                'status' => false,
                'message' => 'لا يوجد مطعم بهذا الاسم في منطقتك',
            ], 404);
        }

        $restaurant = $restaurantUser->restaurant;

        $categories = Category::whereHas('meals', function ($q) use ($restaurant) {
            $q->where('restaurant_id', $restaurant->id);
        })
            ->with(['meals' => function ($q) use ($restaurant) {
                $q->where('restaurant_id', $restaurant->id);
            }, 'meals.images'])
            ->get();

        return response()->json([
            'status' => true,
            'restaurant' => [
                'name' => $restaurantUser->fullname,
                'details' => $restaurant,
                'city' => $city,
            ],
            'categories' => $categories->map(function ($category) {
                return [
                    'category_name' => $category->name,
                    'meals' => $category->meals->map(function ($meal) {
                        return [
                            'meal_name' => $meal->name,
                            'price' => $meal->original_price,
                            'images' => $meal->images->map(function ($image) {
                                return asset('storage/' . $image->image);
                            }),
                        ];
                    }),
                ];
            })
        ], 200);
    }


    public function searchMealByName(Request $request, $city)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        $mealName = $request->name;

        $areaIds = Area::where('city', $city)->pluck('id');

        if ($areaIds->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'لا توجد مناطق لهذه المدينة.',
            ], 404);
        }

        $restaurants = User::whereHas('areas', function ($query) use ($areaIds) {
            $query->whereIn('areas.id', $areaIds);
        })
            ->whereHas('restaurant.meals', function ($query) use ($mealName) {
                $query->where('name', 'LIKE', '%' . $mealName . '%');
            })
            ->with([
                'restaurant.meals' => function ($query) use ($mealName) {
                    $query->where('name', 'LIKE', '%' . $mealName . '%')
                        ->with('images');
                },
                'restaurant',
            ])
            ->get();

        if ($restaurants->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'لا توجد مطاعم تقدم هذه الوجبة في هذه المدينة.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'restaurants' => $restaurants,
        ], 200);
    }
}
