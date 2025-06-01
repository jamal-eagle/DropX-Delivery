<?php

namespace App\Console\Commands;

use App\Models\Driver;
use App\Models\DriverAreaTurn;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RotateDailyDriverTurns extends Command
{

    protected $signature = 'drivers:rotate-daily';

    protected $description = 'تدوير الدور بين السائقين كل يوم الساعة 6 صباحًا فقط لمن لديهم دوام اليوم';


    public function handle()
    {
        $today = now()->format('l'); // Sunday, Monday...

        // جلب جميع الأدوار النشطة المرتبطة بسائقين لديهم دوام اليوم
        $eligibleTurns = DriverAreaTurn::where('is_active', true)
            ->whereHas('driver.workingHours', function ($q) use ($today) {
                $q->where('day_of_week', $today);
            })
            ->with(['driver.workingHours', 'driver.user.areas'])
            ->get();

        // تجميع الأدوار بحسب المدينة
        $groupedByCity = $eligibleTurns->groupBy(function ($turn) {
            return strtolower(trim(optional($turn->driver->user->areas->first())->city));
        });

        foreach ($groupedByCity as $city => $turns) {
            if ($city === '' || $turns->isEmpty()) {
                Log::warning("❌ لم يتم تحديد المدينة بشكل صحيح لبعض السائقين");
                continue;
            }

            // ترتيب حسب turn_order
            $sortedTurns = $turns->sortBy('turn_order')->values();

            if ($sortedTurns->count() === 1) {
                $turn = $sortedTurns->first();
                $turn->update([
                    'is_next' => true,
                    'turn_assigned_at' => now(),
                ]);
                Log::info("🚨 السائق الوحيد في مدينة {$city} تم إبقاء الدور لديه ID {$turn->driver_id}");
                continue;
            }

            // تدوير الدور: أول واحد يصبح هو صاحب الدور
            foreach ($sortedTurns as $i => $turn) {
                $turn->update([
                    'is_next' => $i === 0,
                    'turn_assigned_at' => $i === 0 ? now() : null,
                ]);
            }

            Log::info("✅ تم تدوير الدور في مدينة {$city} بين " . $sortedTurns->count() . " سائقين.");
        }

        $this->info("تم تدوير الأدوار بنجاح");
        return 0;
    }
}

