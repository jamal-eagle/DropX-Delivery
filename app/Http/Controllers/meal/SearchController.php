<?php

namespace App\Http\Controllers\meal;

use App\Http\Controllers\Controller;
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
                'restaurant.categories.meals',
            ])
            ->get();

        $formattedRestaurants = $restaurants->map(function ($user) {
            return [
                'user_info' => [
                    $user
                ],
                'restaurant_info' => [
                    'description' => $user->restaurant
                ],
                'categories' => $user->restaurant->categories->map(function ($category) {
                    return [
                        'category_name' => $category->name,
                        'meals' => $category->meals->map(function ($meal) {
                            return [
                                $meal
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
        ],200);
    }






    public function searchByNameResturant(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string'
        ]);

        $userAreaIds = $user->areas->pluck('id');

        $restaurantUser = User::whereHas('areas', function ($query) use ($userAreaIds) {
            $query->whereIn('areas.id', $userAreaIds);
        })
            ->where('fullname', 'LIKE', '%' . $request->name . '%')
            ->whereHas('restaurant')
            ->with('restaurant')
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
        })->with(['meals' => function ($q) use ($restaurant) {
            $q->where('restaurant_id', $restaurant->id);
        }])->get();

        return response()->json([
            'status' => true,
            'restaurant' => [
                'name' => $restaurantUser->fullname,
                'details' => $restaurant,
            ],
            'categories' => $categories
        ],200);
    }

    public function searchMealByName(Request $request)
    {
        $request->validate([
            'name' => 'required|string'
        ]);

        $user = auth()->user();
        $userAreaIds = $user->areas->pluck('id');

        $restaurants = User::whereHas('areas', function ($query) use ($userAreaIds) {
            $query->whereIn('areas.id', $userAreaIds);
        })
            ->whereHas('restaurant.meals', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->name . '%');
            })
            ->with(['restaurant.meals' => function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->name . '%');
            }, 'restaurant'])
            ->get();

        return response()->json([
            'status' => true,
            'restaurants' => $restaurants
        ],200);
    }
}
