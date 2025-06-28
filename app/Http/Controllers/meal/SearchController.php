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
                'status'  => false,
                'message' => 'التطبيق لا يخدم هذه المدينة.',
            ], 404);
        }

        $restaurants = User::whereHas('areas', fn($q) => $q->where('areas.city', $city))
            ->whereHas('restaurant')
            ->with([
                'restaurant.meals.images',
            ])
            ->get();

        if ($restaurants->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'لا يوجد مطاعم في هذه المدينة.',
            ], 404);
        }

        $formattedRestaurants = $restaurants->map(function ($user) {

            $restaurant = $user->restaurant;
            $imageUrl   = $restaurant->image ? asset('storage/' . $restaurant->image) : null;

            $meals = $restaurant->meals;

            $categoryIds     = $meals->pluck('category_id')->unique();
            $usedCategories  = \App\Models\Category::whereIn('id', $categoryIds)->get();

            $categories = $usedCategories->map(function ($category) use ($meals) {

                $mealsInCat = $meals->where('category_id', $category->id);

                return [
                    'category_name' => $category->name,
                    'meals' => $mealsInCat->map(function ($meal) {
                        return [
                            'meal_id'      => $meal->id,
                            'meal_name'    => $meal->name,
                            'price'        => $meal->original_price,
                            'is_available' => $meal->is_available,
                            'images'       => $meal->images
                                ->map(fn($img) => asset('storage/' . $img->image)),
                        ];
                    })->values(),
                ];
            });

            /* ── بناء الخرج بنفس البنية السابقة ──────────────────── */
            return [
                'restaurant_From_User' => [
                    'id'            => $user->id,
                    'fullname'      => $user->fullname,
                    'phone'         => $user->phone,
                    'location_text' => $user->location_text,
                    'latitude'      => $user->latitude,
                    'longitude'     => $user->longitude,
                    'is_active'     => $user->is_active,
                    'is_verified'   => $user->is_verified,
                    'restaurant'    => [
                        'id'                  => $restaurant->id,
                        'user_id'             => $restaurant->user_id,
                        'image'               => $imageUrl,
                        'description'         => $restaurant->description,
                        'working_hours_start' => $restaurant->working_hours_start,
                        'working_hours_end'   => $restaurant->working_hours_end,
                        'status'              => $restaurant->status,
                        'created_at'          => $restaurant->created_at,
                        'updated_at'          => $restaurant->updated_at,
                        'meals'               => $meals->map(function ($meal) {
                            return [
                                'id'             => $meal->id,
                                'category_id'    => $meal->category_id,
                                'restaurant_id'  => $meal->restaurant_id,
                                'name'           => $meal->name,
                                'original_price' => $meal->original_price,
                                'is_available'   => $meal->is_available,
                                'created_at'     => $meal->created_at,
                                'updated_at'     => $meal->updated_at,
                                'images'         => $meal->images
                                    ->map(fn($img) => asset('storage/' . $img->image)),
                            ];
                        }),
                    ],
                ],
                'categories' => $categories,
            ];
        });

        return response()->json([
            'status'      => true,
            'city'        => $city,
            'restaurants' => $formattedRestaurants,
        ], 200);
    }


    public function searchByNameResturant(Request $request, $city)
    {
        $areaIds = Area::where('city', $city)->pluck('id');
        if ($areaIds->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'التطبيق لا يخدم هذه المدينة.',
            ], 404);
        }

        $request->validate(['name' => 'required|string']);

        $restaurantUsers = User::whereHas('areas', fn($q) => $q->where('areas.city', $city))
            ->where('fullname', 'LIKE', '%' . $request->name . '%')
            ->whereHas('restaurant')
            ->with([
                'restaurant.meals.images',
            ])
            ->get();

        if ($restaurantUsers->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'لا يوجد مطعم بهذا الاسم في المنطقة التي أنت فيها.',
            ], 404);
        }

        $formattedRestaurants = $restaurantUsers->map(function ($user) {

            $restaurant = $user->restaurant;
            $meals      = $restaurant->meals;
            $imageUrl   = $restaurant->image
                ? asset('storage/' . $restaurant->image)
                : null;

            $categoryIds = $meals->pluck('category_id')->unique();
            $usedCategories = Category::whereIn('id', $categoryIds)->get();

            $categories = $usedCategories->map(function ($category) use ($meals) {

                $mealsInCat = $meals->where('category_id', $category->id);

                return [
                    'category_name' => $category->name,
                    'meals' => $mealsInCat->map(function ($meal) {
                        return [
                            'meal_id'      => $meal->id,
                            'meal_name'    => $meal->name,
                            'price'        => $meal->original_price,
                            'is_available' => $meal->is_available,
                            'images'       => $meal->images
                                ->map(fn($img) => asset('storage/' . $img->image)),
                        ];
                    })->values(),
                ];
            });

            return [
                'restaurant_From_User' => [
                    'id'           => $user->id,
                    'fullname'     => $user->fullname,
                    'phone'        => $user->phone,
                    'location_text' => $user->location_text,
                    'latitude'     => $user->latitude,
                    'longitude'    => $user->longitude,
                    'is_active'    => $user->is_active,
                    'is_verified'  => $user->is_verified,
                    'restaurant'   => [
                        'id'                  => $restaurant->id,
                        'user_id'             => $restaurant->user_id,
                        'image'               => $imageUrl,                 // ← رابط كامل
                        'description'         => $restaurant->description,
                        'working_hours_start' => $restaurant->working_hours_start,
                        'working_hours_end'   => $restaurant->working_hours_end,
                        'status'              => $restaurant->status,
                        'created_at'          => $restaurant->created_at,
                        'updated_at'          => $restaurant->updated_at,
                        'meals'               => $meals->map(function ($meal) {
                            return [
                                'id'             => $meal->id,
                                'category_id'    => $meal->category_id,
                                'restaurant_id'  => $meal->restaurant_id,
                                'name'           => $meal->name,
                                'original_price' => $meal->original_price,
                                'is_available'   => $meal->is_available,
                                'created_at'     => $meal->created_at,
                                'updated_at'     => $meal->updated_at,
                                'images'         => $meal->images
                                    ->map(fn($img) => asset('storage/' . $img->image)),
                            ];
                        }),
                        'categories'          => $restaurant->categories,  // تبقى كما كانت
                    ],
                ],
                'categories' => $categories,
            ];
        });

        return response()->json([
            'status'      => true,
            'city'        => $city,
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
