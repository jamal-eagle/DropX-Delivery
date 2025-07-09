<?php

namespace App\Http\Controllers\resturant;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\DriverAreaTurn;
use App\Models\Meal;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Restaurant;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResturantController extends Controller
{
    protected function isDriverInWorkingHours($driver)
    {
        $today = now()->format('l'); // Saturday, Sunday...

        $workingSchedule = $driver->workingHours()
            ->where('day_of_week', $today)
            ->first();

        if (!$workingSchedule) {
            return false;
        }

        $now = now()->format('H:i:s');

        return $now >= $workingSchedule->start_time && $now <= $workingSchedule->end_time;
    }

    private function rotateDriverTurn($currentDriver): bool
    {
        $currentTurn = DriverAreaTurn::where('driver_id', $currentDriver->id)
            ->where('is_active', true)
            ->first();

        if (!$currentTurn) {
            Log::error("❌ لم يتم العثور على دور للسائق ID {$currentDriver->id}.");
            return false;
        }

        $areaId = $currentTurn->area_id;

        $allTurns = DriverAreaTurn::where('is_active', true)
            ->where('area_id', $areaId)
            ->with(['driver.user', 'driver.workingHours'])
            ->orderBy('turn_order')
            ->get();

        $eligibleTurns = $allTurns->filter(function ($turn) use ($currentDriver) {
            $driver = $turn->driver;

            return $driver
                && $driver->id !== $currentDriver->id
                && $driver->is_active
                && $this->isDriverInWorkingHours($driver);
        });

        if ($eligibleTurns->isEmpty()) {
            $currentTurn->update([
                'is_next' => true,
                'turn_assigned_at' => now(),
            ]);

            Log::info("🚫 لا يوجد سائق متاح في المنطقة ID {$areaId} لتدوير الدور.");
            return false;
        }

        $nextTurn = $eligibleTurns->first();

        $currentTurn->update([
            'is_next' => false,
            'turn_assigned_at' => null,
        ]);

        $nextTurn->update([
            'is_next' => true,
            'turn_assigned_at' => now(),
        ]);

        Log::info("✅ تم تدوير الدور من السائق ID {$currentDriver->id} إلى السائق ID {$nextTurn->driver_id} في المنطقة ID {$areaId}");

        return true;
    }

    public function getPreparingOrders()
    {
        $restaurantId = auth()->user()->restaurant->id;

        $orders = Cache::remember("preparing_orders_restaurant_{$restaurantId}", now()->addMinutes(15), function () use ($restaurantId) {
            return Order::with(['user', 'orderItems.meal.images'])
                ->where('restaurant_id', $restaurantId)
                ->where('status', 'preparing')
                ->latest()
                ->get()
                ->map(function ($order) {
                    $order->barcode = $order->barcode ? asset('storage/' . $order->barcode) : null;
                    return $order;
                });
        });

        return response()->json([
            'status' => true,
            'orders' => $orders
        ], 200);
    }


    public function getPendingOrders()
    {
        $restaurantId = auth()->user()->restaurant->id;

        $orders = Cache::remember("pending_orders_restaurant_{$restaurantId}", now()->addMinutes(15), function () use ($restaurantId) {
            return Order::with(['user', 'orderItems.meal.images'])
                ->where('restaurant_id', $restaurantId)
                ->where('status', 'pending')
                ->latest()
                ->get()->map(function ($order) {
                    $order->barcode = $order->barcode ? asset('storage/' . $order->barcode) : null;
                    return $order;
                });
        });

        return response()->json([
            'status' => true,
            'orders' => $orders
        ], 200);
    }

    // public function acceptOrder($orderId)
    // {
    //     $restaurant = auth()->user()->restaurant;

    //     $order = Order::where('id', $orderId)
    //         ->where('restaurant_id', $restaurant->id)
    //         ->where('status', 'pending')
    //         ->first();

    //     if (!$order) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'الطلب غير موجود أو تم قبوله مسبقًا.',
    //         ], 404);
    //     }

    //     $order->status = 'preparing';
    //     $order->is_accepted = true;
    //     $order->save();

    //     Cache::forget("pending_orders_restaurant_{$restaurant->id}");
    //     Cache::forget("preparing_orders_restaurant_{$restaurant->id}");

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'تم قبول الطلب بنجاح',
    //         'order' => $order,
    //     ], 200);
    // }

    public function acceptOrder($orderId)
    {
        try {
            $result = DB::transaction(function () use ($orderId) {
                $restaurant = auth()->user()->restaurant;

                $order = Order::where('id', $orderId)
                    ->where('restaurant_id', $restaurant->id)
                    ->where('status', 'pending')
                    ->first();

                if (!$order) {
                    return response()->json([
                        'status' => false,
                        'message' => 'الطلب غير موجود أو تم قبوله مسبقًا.',
                    ], 404);
                }

                $order->status = 'preparing';
                $order->is_accepted = true;

                $restaurantCity = optional($restaurant->user->areas()->first())->city;

                if (!$restaurantCity) {
                    return response()->json([
                        'status' => false,
                        'message' => 'لا يمكن تحديد المدينة الخاصة بالمطعم.',
                    ], 400);
                }

                $candidateDrivers = Driver::whereHas('user.areas', function ($query) use ($restaurantCity) {
                    $query->whereRaw('LOWER(TRIM(city)) = ?', [strtolower(trim($restaurantCity))]);
                })
                    ->whereHas('areaTurns', function ($q) {
                        $q->where('is_next', true)->where('is_active', true);
                    })
                    ->with([
                        'areaTurns' => function ($q) {
                            $q->where('is_next', true)->where('is_active', true);
                        },
                        'user.areas'
                    ])
                    ->get();

                $availableDriver = null;

                foreach ($candidateDrivers as $driver) {
                    $turn = $driver->areaTurns->first();

                    if (
                        $driver->is_active &&
                        $this->isDriverInWorkingHours($driver)
                    ) {
                        $availableDriver = $driver;
                        break;
                    }
                }

                if (!$availableDriver) {
                    return response()->json([
                        'status' => false,
                        'message' => 'لا يوجد سائق متاح حاليًا في المدينة.',
                    ], 422);
                }

                $order->driver_id = $availableDriver->id;
                $order->save();

                $availableDriver->areaTurns->first()->update([
                    'turn_assigned_at' => now(),
                ]);

                $this->rotateDriverTurn($availableDriver);

                $customer = $order->user;
                if ($customer && $customer->fcm_token) {
                    $title = 'تم قبول طلبك';
                    $body  = "المطعم {$restaurant->user->fullname} بدأ تجهيز طلبك رقم #{$order->id}.";
                    $data  = ['type' => 'order_accepted', 'order_id' => $order->id];

                    app(FirebaseNotificationService::class)
                        ->sendToToken($customer->fcm_token, $title, $body, $data, $customer->id);

                    Notification::create([
                        'user_id' => $customer->id,
                        'title'   => $title,
                        'body'    => $body,
                        'data'    => $data,
                    ]);
                }

                $driverUser = $availableDriver->user;
                if ($driverUser && $driverUser->fcm_token) {
                    $title = 'طلب جديد بحاجة الى توصيل';
                    $body  = "لديك طلب رقم #{$order->id} للتوصيل. اضغط لعرض التفاصيل.";
                    $data  = ['type' => 'new_delivery', 'order_id' => $order->id];

                    Notification::create([
                        'user_id' => $driverUser->id,
                        'title'   => $title,
                        'body'    => $body,
                        'data'    => $data,
                    ]);
                }

                app(FirebaseNotificationService::class)
                    ->sendToToken($driverUser->fcm_token, $title, $body, $data, $driverUser->id);



                Cache::forget("pending_orders_restaurant_{$restaurant->id}");
                Cache::forget("preparing_orders_restaurant_{$restaurant->id}");

                return response()->json([
                    'status' => true,
                    'message' => 'تم قبول الطلب وتعيين سائق.',
                    'order' => $order,
                ], 200);
            });

            return $result;
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ غير متوقع أثناء قبول الطلب.',
            ], 500);
        }
    }


    public function rejectOrder($orderId)
    {
        $restaurant = auth()->user()->restaurant;

        $order = Order::where('id', $orderId)
            ->where('restaurant_id', $restaurant->id)
            ->where('status', 'pending')
            ->first();

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'الطلب غير موجود .',
            ], 404);
        }
        $order->status = 'rejected';
        $order->is_accepted = false;
        $order->save();
        $customer = $order->user;

        if ($customer && $customer->fcm_token) {
            $title = 'عذرًا، تم رفض طلبك';
            $body  = "المطعم {$restaurant->user->fullname} رفض طلبك   #.";
            $data  = ['type' => 'order_rejected', 'order_id' => $order->id];

            app(FirebaseNotificationService::class)
                ->sendToToken($customer->fcm_token, $title, $body, $data, $customer->id);

            Notification::create([
                'user_id' => $customer->id,
                'title'   => $title,
                'body'    => $body,
                'data'    => $data,
            ]);
        }

        Cache::forget("pending_orders_restaurant_{$restaurant->id}");

        return response()->json([
            'status' => true,
            'message' => 'تم رفض الطلب بنجاح',
        ], 200);
    }

    public function getOrderDetails($orderId)
    {
        $restaurant = auth()->user()->restaurant;

        if (!$restaurant) {
            return response()->json(['message' => 'لا تملك صلاحية الوصول.'], 403);
        }

        $order = Order::with(['user', 'orderItems.meal.images'])
            ->where('restaurant_id', $restaurant->id)
            ->where('id', $orderId)
            ->first();
        if (!$order) {
            return response()->json(['message' => 'الطلب غير موجود.'], 404);
        }

        $order1 = Order::where('id', $orderId)->first();
        if (!$order1) {
            return response()->json(['message' => 'الطلب غير موجود.'], 404);
        }

        // ✅ تعديل الصور داخل كل وجبة إلى روابط asset
        foreach ($order->orderItems as $orderItem) {
            foreach ($orderItem->meal->images as $image) {
                $image->image = asset('storage/' . $image->image);
            }
        }

        // ✅ تعديل الباركود إلى رابط asset
        if ($order1->barcode) {
            $order1->barcode = asset('storage/' . $order1->barcode);
        }

        $items = $order->orderItems->map(function ($item) {
            return [
                'meal_name' => $item->meal->name,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'total' => $item->price * $item->quantity,
                'images' => $item->meal->images->pluck('image'),
            ];
        });

        $totalQuantity = $order->orderItems->sum('quantity');
        $distinctTypes = $order->orderItems->count();

        return response()->json([
            'order' => $order1,
            'customer' => [
                'fullname' => $order->user->fullname,
                'phone' => $order->user->phone,
            ],
            'meals_summary' => [
                'types_count' => $distinctTypes,
                'total_quantity' => $totalQuantity,
                'total_price' => $order->total_price
            ],
            'items' => $items,
            'notes' => $order->notes,
            'status' => $order->status,
            'created_at' => $order->created_at->format('Y-m-d H:i'),
        ], 200);
    }


    public function updateWorkingHours(Request $request)
    {
        $request->validate([
            'working_hours_start' => 'nullable|date_format:H:i',
            'working_hours_end'   => 'nullable|date_format:H:i',
        ]);

        $user = auth()->user();

        if (!$user->restaurant) {
            return response()->json(['message' => 'هذا المستخدم ليس مطعم.'], 403);
        }

        $restaurant = $user->restaurant;

        if ($request->filled('working_hours_start') && $request->filled('working_hours_end')) {
            if (strtotime($request->working_hours_end) <= strtotime($request->working_hours_start)) {
                return response()->json(['message' => 'وقت الإغلاق يجب أن يكون بعد وقت الفتح.'], 400);
            }
        }

        if ($request->filled('working_hours_start')) {
            $restaurant->working_hours_start = $request->working_hours_start;
        }

        if ($request->filled('working_hours_end')) {
            $restaurant->working_hours_end = $request->working_hours_end;
        }

        $restaurant->save();

        return response()->json([
            'message' => 'تم تحديث ساعات العمل بنجاح.',
            'restaurant' => $restaurant
        ], 200);
    }

    public function updateResturantStatusClose()
    {
        $restaurant = auth()->user()->restaurant;

        if (!$restaurant) {
            return response()->json(['message' => 'لا تملك صلاحية الوصول.'], 403);
        }
        if ($restaurant->status === 'closed') {
            return response()->json('المطعم مغلق لا يلزم تعديل الحالة ', 400);
        }
        $restaurant->update([
            'status' => 'closed'
        ]);
        return response()->json('تم تعديل حالة المطعم باغلاقه ', 200);
    }

    public function updateResturantStatusOpen()
    {
        $restaurant = auth()->user()->restaurant;

        if (!$restaurant) {
            return response()->json(['message' => 'لا تملك صلاحية الوصول.'], 403);
        }
        if ($restaurant->status === 'open') {
            return response()->json('المطعم مفتوح  لا يلزم تعديل الحالة ', 400);
        }
        $restaurant->update([
            'status' => 'open'
        ]);
        return response()->json('تم تعديل حالة المطعم بفتحه ', 200);
    }

    public function desplayMyMeals()
    {
        $restaurant = Auth::user()->restaurant;
        if (!$restaurant) {
            return response()->json(['message' => 'لا تملك صلاحيات الوصول'], 403);
        }
        $meals = Meal::with('images')
            ->where('restaurant_id', $restaurant->id)
            ->latest()
            ->get();

        $formattedMeals = $meals->map(function ($meal) {
            return [
                'id' => $meal->id,
                'name' => $meal->name,
                'price' => $meal->original_price,
                'is_available' => $meal->is_available,
                'category_id' => $meal->category_id,
                'images' => $meal->images->map(function ($img) {
                    return asset('storage/' . $img->image);
                }),
            ];
        });

        return response()->json([
            'status' => true,
            'meals' => $formattedMeals,
        ], 200);
    }


    public function updateMealStatusAndPrice(Request $request, $mealId)
    {
        $restaurant = Auth::user()->restaurant;

        if (!$restaurant) {
            return response()->json(['message' => 'لا تملك صلاحيات الوصول'], 403);
        }

        $meal = Meal::where('id', $mealId)
            ->where('restaurant_id', $restaurant->id)
            ->first();

        if (!$meal) {
            return response()->json([
                'status' => false,
                'message' => 'الوجبة غير موجودة أو لا تتبع لهذا المطعم.',
            ], 404);
        }

        $validated = $request->validate([
            'is_available' => 'nullable|boolean',
            'new_price' => 'nullable|numeric|min:0',
        ]);

        $updatedFields = [];

        if ($request->has('is_available')) {
            $meal->is_available = $validated['is_available'];
            $updatedFields[] = 'الحالة';
        }

        if ($request->has('new_price')) {
            $meal->original_price = $validated['new_price'];
            $updatedFields[] = 'السعر';
        }

        if (empty($updatedFields)) {
            return response()->json([
                'status' => false,
                'message' => 'لم يتم إرسال أي تعديل.',
            ], 400);
        }

        $meal->save();

        return response()->json([
            'status' => true,
            'message' => 'تم تحديث ' . implode(' و', $updatedFields) . ' بنجاح.',
            'meal' => [
                'id' => $meal->id,
                'name' => $meal->name,
                'is_available' => $meal->is_available ? 'متاح' : 'غير متاح',
                'original_price' => $meal->original_price,
            ],
        ], 200);
    }



    public function getResturantProfile()
    {
        $user = auth()->user()->load('restaurant');

        if (!$user || !$user->restaurant) {
            return response()->json('هذا ليس حساب مطعم ', 403);
        }

        $restaurant = $user->restaurant;
        $restaurantData = $restaurant->toArray();

        // تحويل الصورة إلى رابط كامل
        $image = $restaurantData['image'] = $restaurant->image
            ? asset('storage/' . $restaurant->image)
            : null;

        return response()->json([
            'status' => true,
            'user' => $user,
            'image' => $image,
        ], 200);
    }


    public function getRestaurantArchiveOrders()
    {
        $user = auth()->user();

        if (!$user->restaurant) {
            return response()->json([
                'status'  => false,
                'message' => 'هذا المستخدم لا يملك مطعم.',
            ], 403);
        }

        $restaurantId = $user->restaurant->id;
        $statuses     = ['rejected', 'delivered', 'on_delivery'];

        $orders = Order::with([
            'user',
            'driver.user',
            'orderItems.meal.images',
        ])
            ->where('restaurant_id', $restaurantId)
            ->whereIn('status', $statuses)
            ->latest()
            ->get()
            ->map(function ($order) {
                if ($order->barcode) {
                    $order->barcode = asset('storage/' . $order->barcode);
                }

                foreach ($order->orderItems as $orderItem) {
                    if ($orderItem->meal && $orderItem->meal->images) {
                        foreach ($orderItem->meal->images as $image) {
                            $image->image = asset('storage/' . $image->image);
                        }
                    }
                }

                return $order;
            });

        return response()->json([
            'status' => true,
            'orders' => $orders,
        ]);
    }
}
