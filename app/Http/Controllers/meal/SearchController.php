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
        $areaIds = Area::where('city', $city)->pluck('id');

        if ($areaIds->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'التطبيق لا يخدم هذه المدينة.',
            ], 404);
        }
        $restaurants = User::whereHas('areas', function ($query) use ($city) {
            $query->where('city', $city);
        })
            ->whereHas('restaurant')
            ->with([
                'restaurant.meals.images',
                'restaurant.categories.meals.images',
            ])
            ->get();

        if ($restaurants->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'لا يوجد مطاعم في هذه المدينة.',
            ], 404);
        }

        $formattedRestaurants = $restaurants->map(function ($user) {
            $restaurant = $user->restaurant;

            $allMeals = $restaurant->meals->map(function ($meal) {
                return [
                    'meal_id' => $meal->id,
                    'meal_name' => $meal->name,
                    'price' => $meal->original_price,
                    'is_available' => $meal->is_available,
                    'images' => $meal->images->map(function ($img) {
                        return asset('storage/' . $img->image);
                    }),
                ];
            });

            $categories = $restaurant->categories->map(function ($category) {
                return [
                    'category_name' => $category->name,
                    'meals' => $category->meals->map(function ($meal) {
                        return [
                            'meal_id' => $meal->id,
                            'meal_name' => $meal->name,
                            'price' => $meal->original_price,
                            'is_available' => $meal->is_available,
                            'images' => $meal->images->map(function ($img) {
                                return asset('storage/' . $img->image);
                            }),
                        ];
                    }),
                ];
            });

            return [
                'restaurant_From_User' => $restaurant->user,
                'resturant_info' => $restaurant,
                'all_meals' => $allMeals,
                'categories' => $categories,
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

        $areaIds = Area::where('city', $city)->pluck('id');

        if ($areaIds->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'التطبيق لا يخدم هذه  المدينة.',
            ], 404);
        }

        $request->validate([
            'name' => 'required|string',
        ]);

        $restaurantUsers = User::whereHas('areas', function ($query) use ($city) {
            $query->where('areas.city', $city);
        })
            ->where('fullname', 'LIKE', '%' . $request->name . '%')
            ->whereHas('restaurant')
            ->with([
                'restaurant.meals.images',
                'restaurant.categories.meals.images'
            ])
            ->get();

        if ($restaurantUsers->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'لا يوجد مطعم بهذا الاسم في المنطقة التي انت فيها',
            ], 404);
        }

        $formattedRestaurants = $restaurantUsers->map(function ($user) {
            $restaurant = $user->restaurant;

            $allMeals = $restaurant->meals->map(function ($meal) {
                return [
                    'meal_id' => $meal->id,
                    'meal_name' => $meal->name,
                    'price' => $meal->original_price,
                    'is_available' => $meal->is_available,
                    'images' => $meal->images->map(function ($img) {
                        return asset('storage/' . $img->image);
                    }),
                ];
            });

            $categories = $restaurant->categories->map(function ($category) use ($restaurant) {
                $meals = $category->meals->where('restaurant_id', $restaurant->id);

                return [
                    'category_name' => $category->name,
                    'meals' => $meals->map(function ($meal) {
                        return [
                            'meal_id' => $meal->id,
                            'meal_name' => $meal->name,
                            'price' => $meal->original_price,
                            'is_available' => $meal->is_available,
                            'images' => $meal->images->map(function ($image) {
                                return asset('storage/' . $image->image);
                            }),
                        ];
                    })->values(),
                ];
            });

            return [
                'restaurant_From_User' => $user,
                'resturant_info' => $restaurant,
                'all_meals' => $allMeals,
                'categories' => $categories,
            ];
        });

        return response()->json([
            'status' => true,
            'city' => $city,
            'restaurants' => $formattedRestaurants,
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
                'message' => 'التطبيق لا يخدم هذه المدينة.',
            ], 404);
        }

        $restaurantUsers = User::whereHas('areas', function ($query) use ($areaIds) {
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

        if ($restaurantUsers->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'لا توجد مطاعم تقدم هذه الوجبة في هذه المدينة.',
            ], 404);
        }

        $data = $restaurantUsers->map(function ($user) {
            return [
                'restaurant_user' => [
                    'Resturant_From_User' => $user,
                ],
                'matched_meals' => $user->restaurant->meals->map(function ($meal) {
                    return [
                        'meal_id' => $meal->id,
                        'meal_name' => $meal->name,
                        'price' => $meal->original_price,
                        'is_available' => $meal->is_available,
                        'images' => $meal->images->map(function ($img) {
                            return asset('storage/' . $img->image);
                        }),
                    ];
                }),
            ];
        });

        return response()->json([
            'restaurants' => $data,
        ], 200);
    }

}
