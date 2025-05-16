<?php

namespace App\Http\Controllers\order;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Area;
use App\Models\Meal;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Endroid\QrCode\Builder\Builder;
use Illuminate\Support\Facades\Cache;

class OrderController extends Controller
{
    public function createOrderWithPromo(Request $request)
    {
        $request->validate([
            'meals' => 'required|array|min:1',
            'meals.*.id' => 'required|exists:meals,id',
            'meals.*.quantity' => 'required|integer|min:1',
            'address_id' => 'required|exists:area_user,id',
            'notes' => 'nullable|string',
            'promo_code' => 'nullable|string',
        ]);

        $user = auth()->user();
        DB::beginTransaction();

        try {
            $totalPrice = 0;
            $restaurantId = null;
            $promo = null;
            $discount = 0;

            $mealIds = collect($request->meals)->pluck('id')->toArray();
            $meals = Meal::whereIn('id', $mealIds)->get()->keyBy('id');

            foreach ($request->meals as $item) {
                $meal = $meals->get($item['id']);

                if (!$meal) {
                    return response()->json(['message' => 'وجبة غير موجودة.'], 404);
                }

                if (!$restaurantId) {
                    $restaurantId = $meal->restaurant_id;
                } elseif ($restaurantId != $meal->restaurant_id) {
                    return response()->json(['message' => 'كامل الطلب يجب أن يكون من نفس المطعم.'], 400);
                }

                $totalPrice += $meal->original_price * $item['quantity'];
            }

            $address = DB::table('area_user')
                ->join('areas', 'area_user.area_id', '=', 'areas.id')
                ->where('area_user.id', $request->address_id)
                ->where('area_user.user_id', $user->id)
                ->select('areas.city', 'areas.neighborhood')
                ->first();

            if (!$address) {
                return response()->json(['message' => 'العنوان غير موجود.'], 404);
            }

            if ($request->filled('promo_code')) {
                $promo = PromoCode::where('code', $request->promo_code)
                    ->where('is_active', true)
                    ->where('expiry_date', '>', now())
                    ->first();

                if (!$promo) {
                    return response()->json(['message' => 'كود الخصم غير صالح أو منتهي.'], 404);
                }

                if ($totalPrice < $promo->min_order_value) {
                    return response()->json(['message' => 'قيمة الطلب أقل من الحد الأدنى لهذا الكود.'], 422);
                }

                $alreadyUsed = DB::table('user_promo_codes')
                    ->where('user_id', $user->id)
                    ->where('promo_code_id', $promo->id)
                    ->where('is_used', true)
                    ->exists();

                if ($alreadyUsed) {
                    return response()->json(['message' => 'لقد استخدمت هذا الكود مسبقًا.'], 403);
                }

                $discount = $promo->discount_type === 'percentage'
                    ? $totalPrice * ($promo->discount_value / 100)
                    : $promo->discount_value;

                $totalPrice -= $discount;
            }

            $barcodeText = 'order-' . Str::uuid();
            $barcodePath = 'barcodes/' . $barcodeText . '.png';
            $result = Builder::create()->data($barcodeText)->size(300)->margin(10)->build();
            Storage::disk('public')->put($barcodePath, $result->getString());
            $order = Order::create([
                'user_id' => $user->id,
                'restaurant_id' => $restaurantId,
                'driver_id' => null,
                'status' => 'pending',
                'is_accepted' => false,
                'total_price' => $totalPrice,
                'delivery_address' => "{$address->city} - {$address->neighborhood}",
                'notes' => $request->notes,
                'delivery_fee' => 10000,
                'barcode' => $barcodePath,
            ]);

            foreach ($request->meals as $item) {
                $meal = $meals->get($item['id']);

                $order->orderItems()->create([
                    'meal_id' => $meal->id,
                    'quantity' => $item['quantity'],
                    'price' => $meal->original_price,
                ]);
            }

            if ($promo) {
                DB::table('promo_codes')->where('id', $promo->id)->decrement('max_uses');
                DB::table('user_promo_codes')->insert([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'promo_code_id' => $promo->id,
                    'fcm_token' => $user->fcm_token,
                    'is_used' => true,
                    'used_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            Cache::forget("pending_orders_restaurant_{$restaurantId}");
            DB::commit();

            return response()->json([
                'message' => 'تم إنشاء الطلب' . ($promo ? ' مع تطبيق كود الخصم' : '') . ' بنجاح',
                'order_Details' => $order,
                'original_price' => round($totalPrice + $discount, 2),
                'discount' => round($discount, 2),
                'final_price' => round($totalPrice, 2) + $order->delivery_fee,
                'barcode_url' => asset('storage/' . $barcodePath),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'حدث خطأ أثناء إنشاء الطلب',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }


    public function updateOrder(UpdateOrderRequest $request, $order_id)
    {

        $user = auth()->user();

        $order = Order::where('id', $order_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'الطلب غير موجود.'], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'لا يمكن تعديل الطلب إلا إذا كان في مرحلة الانتظار.'], 400);
        }

        DB::beginTransaction();

        try {
            // ✅ تعديل العنوان إن وجد
            if ($request->filled('address_id')) {
                $address = DB::table('area_user')
                    ->join('areas', 'area_user.area_id', '=', 'areas.id')
                    ->where('area_user.id', $request->address_id)
                    ->where('area_user.user_id', $user->id)
                    ->select('areas.city', 'areas.neighborhood')
                    ->first();

                if (!$address) {
                    return response()->json(['message' => 'العنوان غير صالح.'], 404);
                }

                $order->delivery_address = "{$address->city} - {$address->neighborhood}";
            }

            if ($request->filled('notes')) {
                $order->notes = $request->notes;
            }

            $order->save();
            $restaurantId = $order->restaurant_id;
            Cache::forget("pending_orders_restaurant_{$restaurantId}");
            DB::commit();

            return response()->json([
                'message' => 'تم تعديل الطلب بنجاح.',
                'order' => $order->load('orderItems.meal'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'حدث خطأ أثناء تعديل الطلب.',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }

    public function updateOrAddMealToOrder(Request $request, $orderId)
    {
        try {
            $request->validate([
                'meals' => 'required|array|min:1',
                'meals.*.meal_id' => 'required|exists:meals,id',
                'meals.*.quantity' => 'required|integer|min:1',
            ]);

            $user = auth()->user();
            $order = Order::where('id', $orderId)->where('user_id', $user->id)->first();

            if (!$order) {
                return response()->json(['message' => 'الطلب غير موجود'], 404);
            }

            if (!in_array($order->status, ['pending', 'preparing'])) {
                return response()->json(['message' => 'لا يمكن تعديل هذا الطلب في حالته الحالية'], 403);
            }

            DB::beginTransaction();

            $userPromo = DB::table('user_promo_codes')
                ->where('user_id', $user->id)
                ->where('order_id', $order->id)
                ->where('is_used', true)
                ->first();

            if ($userPromo) {
                DB::table('promo_codes')
                    ->where('id', $userPromo->promo_code_id)
                    ->increment('max_uses');

                DB::table('user_promo_codes')
                    ->where('id', $userPromo->id)
                    ->delete();
            }

            foreach ($request->meals as $item) {
                $meal = Meal::findOrFail($item['meal_id']);
                $existingItem = $order->orderItems()->where('meal_id', $meal->id)->first();

                if ($existingItem) {
                    $existingItem->update([
                        'quantity' => $item['quantity'],
                        'price' => $meal->original_price,
                    ]);
                } else {
                    $order->orderItems()->create([
                        'meal_id' => $meal->id,
                        'quantity' => $item['quantity'],
                        'price' => $meal->original_price,
                    ]);
                }
            }

            $total = $order->orderItems->sum(fn($item) => $item->quantity * $item->price);
            $order->update(['total_price' => $total]);
            $restaurantId = $order->restaurant_id;
            Cache::forget("pending_orders_restaurant_{$restaurantId}");
            DB::commit();

            return response()->json([
                'message' => 'تم تعديل الوجبات بنجاح',
                'order' => $order->load('orderItems.meal'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'حدث خطأ أثناء التعديل',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }


    public function deleteOrderMeals(Request $request, $orderId)
    {
        try {
            $request->validate([
                'meal_ids' => 'required|array|min:1',
                'meal_ids.*' => 'required|exists:meals,id',
            ]);

            $user = auth()->user();
            $order = Order::where('id', $orderId)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                return response()->json(['message' => 'الطلب غير موجود'], 404);
            }

            if (!in_array($order->status, ['pending', 'preparing'])) {
                return response()->json(['message' => 'لا يمكن حذف وجبات في هذه المرحلة'], 403);
            }

            DB::beginTransaction();
            $userPromo = DB::table('user_promo_codes')
                ->where('user_id', $user->id)
                ->where('order_id', $order->id)
                ->where('is_used', true)
                ->first();

            if ($userPromo) {
                DB::table('promo_codes')
                    ->where('id', $userPromo->promo_code_id)
                    ->increment('max_uses');

                DB::table('user_promo_codes')
                    ->where('id', $userPromo->id)
                    ->delete();
            }

            $order->orderItems()->whereIn('meal_id', $request->meal_ids)->delete();

            $newTotal = $order->orderItems->sum(function ($item) {
                return $item->quantity * $item->price;
            });

            $order->update(['total_price' => $newTotal]);
            $restaurantId = $order->restaurant_id;
            Cache::forget("pending_orders_restaurant_{$restaurantId}");
            DB::commit();

            return response()->json([
                'message' => 'تم حذف الوجبة/الوجبات بنجاح',
                'new_total_price' => round($newTotal, 2),
                'order' => $order->load('orderItems.meal'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'حدث خطأ أثناء الحذف',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ], 500);
        }
    }


    public function getMyOrders()
    {
        $user = auth()->user();

        $orders = Order::where('user_id', $user->id)
            ->with([
                'restaurant.user:id,fullname',
                'orderItems.meal:id,name,original_price',
                'orderItems.meal.images:id,meal_id,image',

            ])
            ->orderBy('created_at', 'desc')
            ->get();

        $result = $orders->map(function ($order) {
            return [
                'order_id' => $order->id,
                'status' => $order->status,
                'is_accepted' => $order->is_accepted,
                'total_price' => $order->total_price,
                'delivery_fee' => $order->delivery_fee,
                'delivery_address' => $order->delivery_address,
                'final_account' => $order->delivery_fee + $order->total_price,
                'notes' => $order->notes,
                'barcode_url' => asset('storage/' . $order->barcode),
                'restaurant_name' => $order->restaurant->user->fullname ?? null,
                'meals' => $order->orderItems->map(function ($item) {
                    return [
                        'meal_name' => $item->meal->name ?? 'غير متوفرة',
                        'price' => $item->price,
                        'quantity' => $item->quantity,
                        'images' => $item->meal->images->map(function ($image) {
                            return asset('storage/' . $image->image);
                        }),
                    ];
                }),
                'created_at' => $order->created_at,
            ];
        });

        return response()->json([
            'orders' => $result,
        ], 200);
    }

    public function getCompletedOrdersForUser()
    {
        $user = auth()->user();

        $orders = Order::with(['restaurant.user', 'orderItems.meal.images'])
            ->where('user_id', $user->id)
            ->where('status', 'delivered')
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'orders' => $orders->map(function ($order) use ($user) {
                $promoUsed = DB::table('user_promo_codes')
                    ->where('user_id', $user->id)
                    ->where('order_id', $order->id)
                    ->where('is_used', true)
                    ->exists();

                $promoUsed1 = DB::table('user_promo_codes')
                    ->where('user_id', $user->id)
                    ->where('order_id', $order->id)
                    ->where('is_used', true)
                    ->get();

                return [
                    'order_id' => $order->id,
                    'restaurant_name' => optional($order->restaurant->user)->fullname,
                    'total_price' => $order->total_price,
                    'delifery_fee' => $order->delivery_fee,
                    'final_price' => $order->total_price  + $order->delivery_fee,
                    'delivery_address' => $order->delivery_address,
                    'delivered_at' => $order->updated_at,
                    'promo_used' => $promoUsed,
                    'promo_Details' => $promoUsed1,
                    'meals' => $order->orderItems->map(function ($item) {
                        return [
                            'name' => optional($item->meal)->name,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'images' => optional($item->meal->images)->map(function ($img) {
                                return asset('storage/' . $img->image);
                            }),
                        ];
                    }),
                ];
            }),
        ]);
    }

    public function getMealsByCity($city)
    {
        $areaIds = Area::where('city', $city)->pluck('id');

        if ($areaIds->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'لا توجد منطقة بهذا الاسم او التطبيق لا يخدم المنطقة',
            ], 404);
        }

        $restaurantUsers = User::where('user_type', 'restaurant')
            ->whereHas('areas', function ($query) use ($areaIds) {
                $query->whereIn('area_id', $areaIds);
            })
            ->pluck('id');

        $meals = Meal::whereHas('restaurant', function ($q) use ($restaurantUsers) {
            $q->whereIn('user_id', $restaurantUsers);
        })
            ->with(['images', 'restaurant.user'])
            ->get();

        $formattedMeals = $meals->map(function ($meal) {
            return [
                'meal_id' => $meal->id,
                'name' => $meal->name,
                'price' => $meal->original_price,
                'is_available' => $meal->is_available,
                'restaurant_name' => $meal->restaurant->user->fullname ?? 'غير معروف',
                'resturant_Details' => $meal->restaurant,
                'images' => $meal->images->map(function ($img) {
                    return asset('storage/' . $img->image);
                }),
            ];
        });

        return response()->json([
            'status' => true,
            'city' => $city,
            'meals' => $formattedMeals,
        ]);
    }

    public function getAllMeals()
    {
        $meals = Meal::with([
            'images',
            'restaurant.user'
        ])->get();

        $formattedMeals = $meals->map(function ($meal) {
            $restaurant = $meal->restaurant;
            $user = $restaurant?->user;

            return [
                'meal_id' => $meal->id,
                'name' => $meal->name,
                'price' => $meal->original_price,
                'is_available' => $meal->is_available,
                'images' => $meal->images->map(function ($img) {
                    return asset('storage/' . $img->image);
                }),
                'restaurant' => [
                    'restaurant_id' => $restaurant->id ?? null,
                    'description' => $restaurant->description ?? null,
                    'working_hours_start' => $restaurant->working_hours_start ?? null,
                    'working_hours_end' => $restaurant->working_hours_end ?? null,
                    'status' => $restaurant->status ?? null,
                ],
                'owner_info' => [
                    'name' => $user->fullname ?? null,
                    'phone' => $user->phone ?? null,
                    'location_text' => $user->location_text ?? null,
                ],
            ];
        });

        return response()->json([
            'status' => true,
            'meals' => $formattedMeals
        ], 200);
    }
}
