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
        $today = now()->format('l');

        $eligibleTurns = DriverAreaTurn::where('is_active', true)
            ->whereHas('driver.workingHours', function ($q) use ($today) {
                $q->where('day_of_week', $today);
            })
            ->with(['driver.workingHours', 'area'])
            ->get();

        $groupedByArea = $eligibleTurns->groupBy('area_id');

        foreach ($groupedByArea as $areaId => $turns) {
            $areaName = optional($turns->first()->area)->city ?? "غير معروفة";

            if ($turns->isEmpty()) {
                Log::warning("❌ لا يوجد سائقين لمنطقة ID: {$areaId}");
                continue;
            }

            // ترتيب حسب الدور
            $sortedTurns = $turns->sortBy('turn_order')->values();

            if ($sortedTurns->count() === 1) {
                $turn = $sortedTurns->first();
                $turn->update([
                    'is_next' => true,
                    'turn_assigned_at' => now(),
                ]);
                Log::info("🚨 السائق الوحيد في منطقة {$areaName} (ID: {$areaId}) تم إبقاء الدور لديه");
                continue;
            }

            foreach ($sortedTurns as $i => $turn) {
                $turn->update([
                    'is_next' => $i === 0,
                    'turn_assigned_at' => $i === 0 ? now() : null,
                ]);
            }

            Log::info("✅ تم تدوير الدور في منطقة {$areaName} (ID: {$areaId}) بين " . $sortedTurns->count() . " سائقين.");
        }

        $this->info("تم تدوير الأدوار بنجاح حسب المناطق.");
        return 0;
    }

}

