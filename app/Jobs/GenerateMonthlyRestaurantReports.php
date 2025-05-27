<?php

namespace App\Jobs;

use App\Models\Restaurant;
use App\Models\RestaurantDailyReport;
use App\Models\RestaurantMonthlyReport;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateMonthlyRestaurantReports implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
       // نحسب عن الشهر الماضي
        $monthStart = Carbon::now()->subMonthNoOverflow()->startOfMonth()->toDateString();
        $monthEnd   = Carbon::now()->subMonthNoOverflow()->endOfMonth()->toDateString();

        // يتم تخزين الشهر على شكل تاريخ في أول يوم من الشهر
        $monthDate = Carbon::parse($monthStart)->startOfMonth();

        // جلب جميع المطاعم
        $restaurants = Restaurant::with('commission')->get();

        foreach ($restaurants as $restaurant) {
            // جلب التقارير اليومية خلال الشهر
            $dailyReports = RestaurantDailyReport::where('restaurant_id', $restaurant->id)
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->get();

            if ($dailyReports->isEmpty()) {
                continue;
            }

            // تجميع القيم
            $totalOrders = $dailyReports->sum('total_orders');
            $totalAmount = $dailyReports->sum('total_amount');
            $systemEarnings = $dailyReports->sum('system_earnings');
            $restaurantEarnings = $dailyReports->sum('restaurant_earnings');

            $commissionType = $restaurant->commission->type ?? 'percentage';
            $commissionValue = $restaurant->commission->value ?? 0;

            // تخزين التقرير الشهري
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
