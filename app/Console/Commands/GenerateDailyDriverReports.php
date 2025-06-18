<?php

namespace App\Console\Commands;

use App\Models\Driver;
use App\Models\DriverAreaTurn;
use App\Models\DriverCommissionSetting;
use App\Models\DriverDailyReport;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateDailyDriverReports extends Command
{
    protected $signature = 'drivers:generate-daily-report';
    protected $description = 'حساب وتخزين تقارير أرباح السائقين اليومية';

    public function handle()
    {
        $today = Carbon::yesterday()->toDateString();

        $commissionSetting = DriverCommissionSetting::first();
        if (!$commissionSetting) {
            Log::error("❌ لم يتم ضبط إعدادات النسبة للسائقين.");
            $this->error("لم يتم ضبط إعدادات النسبة.");
            return 1;
        }

        // 🟢 جلب كل السائقين المرتبطين بدور (حتى لو كانوا غير نشطين)
        $allDriverTurns = DriverAreaTurn::with('driver')->get();

        foreach ($allDriverTurns as $turn) {
            $driver = $turn->driver;
            if (!$driver) continue; // احتياطاً

            $orders = Order::whereDate('created_at', $today)
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

            DriverDailyReport::updateOrCreate(
                ['driver_id' => $driver->id, 'date' => $today],
                [
                    'total_orders'    => $orderCount,
                    'total_amount'    => $totalDeliveryFees,
                    'driver_earnings' => $driverShare,
                    'admin_earnings'  => $adminShare,
                ]
            );

            Log::info("✅ تم إنشاء تقرير يومي للسائق ID {$driver->id}");
        }

        $this->info("📊 تم إنشاء التقارير اليومية بنجاح لكل السائقين.");
        return 0;
    }
}
