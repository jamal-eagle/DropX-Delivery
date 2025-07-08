<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RestaurantDailyReport;
use App\Models\RestaurantMonthlyReport;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateMonthlyRestaurantReports implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        //
    }


    public function handle(): void
    {
        $monthStart = Carbon::now()->subMonthNoOverflow()->startOfMonth();
        $monthEnd = Carbon::now()->subMonthNoOverflow()->endOfMonth();
        $monthDate = Carbon::now()->subMonthNoOverflow()->startOfMonth();


        $restaurants = Restaurant::with('commission')->get();

        foreach ($restaurants as $restaurant) {
            $orders = Order::where('restaurant_id', $restaurant->id)
                ->where('status', 'delivered')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->get();

            $totalOrders = $orders->count();
            $totalAmount = $orders->sum('total_price');

            $commissionType = $restaurant->commission->type ?? 'percentage';
            $commissionValue = $restaurant->commission->value ?? 0;

            if ($commissionType === 'percentage') {
                $systemEarnings = $totalAmount * ($commissionValue / 100);
            } else {
                $systemEarnings = $commissionValue * $totalOrders;
            }

            $restaurantEarnings = $totalAmount - $systemEarnings;

            if ($totalOrders === 0) {
                $systemEarnings = 0;
                $restaurantEarnings = 0;
            }

            RestaurantMonthlyReport::updateOrCreate(
                [
                    'restaurant_id' => $restaurant->id,
                    'month_date' => $monthDate,
                ],
                [
                    'total_orders' => $totalOrders,
                    'total_amount' => $totalAmount,
                    'commission_type' => $commissionType,
                    'commission_value' => $commissionValue,
                    'system_earnings' => $systemEarnings,
                    'restaurant_earnings' => $restaurantEarnings,
                ]
            );
        }
    }
}
