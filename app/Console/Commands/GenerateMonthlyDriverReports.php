<?php

namespace App\Console\Commands;

use App\Models\DriverAreaTurn;
use App\Models\DriverCommissionSetting;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateMonthlyDriverReports extends Command
{
    protected $signature = 'drivers:generate-monthly-report';
    protected $description = 'حساب وتخزين تقارير أرباح السائقين الشهرية';

    public function handle()
    {
        $monthStart = Carbon::now()->subMonthNoOverflow()->startOfMonth()->toDateString();
        $monthEnd = Carbon::now()->subMonthNoOverflow()->endOfMonth()->toDateString();
        $monthLabel = Carbon::now()->subMonthNoOverflow()->format('Y-m'); // مثال: 2024-05

        $commissionSetting = DriverCommissionSetting::first();
        if (!$commissionSetting) {
            Log::error("❌ لم يتم ضبط إعدادات النسبة للسائقين.");
            $this->error("لم يتم ضبط إعدادات النسبة.");
            return 1;
        }

        $allDriverTurns = DriverAreaTurn::with('driver')->get();

        foreach ($allDriverTurns as $turn) {
            $driver = $turn->driver;
            if (!$driver) continue;

            $orders = Order::whereBetween('created_at', [$monthStart, $monthEnd])
                ->where('status', 'delivered')
                ->where('driver_id', $driver->id)
                ->get();

            $orderCount = $orders->count();
            $totalDeliveryFees = $orders->sum('delivery_fee');

            $driverShare = 0;
            $adminShare = 0;

            if ($totalDeliveryFees > 0) {
                if ($commissionSetting->type === 'percentage') {
                    $driverShare = ($totalDeliveryFees * $commissionSetting->driver_percentage) / 100;
                    $adminShare = $totalDeliveryFees - $driverShare;
                } elseif ($commissionSetting->type === 'fixed') {
                    $driverShare = $orderCount * $commissionSetting->driver_percentage;
                    $adminShare = $totalDeliveryFees - $driverShare;
                    if ($adminShare < 0) $adminShare = 0;
                }
            }

            \App\Models\DriverMonthlyReport::updateOrCreate(
                ['driver_id' => $driver->id, 'month_date' => $monthLabel],
                [
                    'total_orders'    => $orderCount,
                    'total_delivery_fees'    => $totalDeliveryFees,
                    'driver_earnings' => $driverShare,
                    'admin_earnings'  => $adminShare,
                ]
            );

            Log::info("✅ تم إنشاء تقرير شهري للسائق ID {$driver->id}");
        }

        $this->info("📊 تم إنشاء تقارير السائقين الشهرية بنجاح.");
        return 0;
    }
}
