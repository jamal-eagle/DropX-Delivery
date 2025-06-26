<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RestaurantDailyReport;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateDailyRestaurantReports implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }


    public function handle(): void
    {
        $today = Carbon::yesterday()->toDateString(); 
        $start = Carbon::yesterday()->startOfDay();
        $end = Carbon::yesterday()->endOfDay();

        $restaurants = Restaurant::with('commission')->get();

        foreach ($restaurants as $restaurant) {
            $orders = Order::where('restaurant_id', $restaurant->id)
                ->whereBetween('created_at', [$start, $end])
                ->where('status', 'delivered')
                ->get();

            $totalOrders = $orders->count();
            $totalAmount = $orders->sum('total_price');

            $commissionType = $restaurant->commission->type ?? 'percentage';
            $commissionValue = $restaurant->commission->value ?? 0;
            $systemEarnings = 0;

            if ($commissionType === 'percentage') {
                $systemEarnings = $totalAmount * ($commissionValue / 100);
            } else {
                $systemEarnings = $commissionValue * $totalOrders;
            }
            $restaurant_earnings = $totalAmount  - $systemEarnings;

            RestaurantDailyReport::updateOrCreate(
                [
                    'restaurant_id' => $restaurant->id,
                    'date' => $today,
                ],
                [
                    'total_orders' => $totalOrders,
                    'total_amount' => $totalAmount,
                    'commission_type' => $commissionType,
                    'commission_value' => $commissionValue,
                    'system_earnings' => $systemEarnings,
                    'restaurant_earnings' => $restaurant_earnings,
                ]
            );
        }
    }
}
