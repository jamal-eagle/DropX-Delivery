<?php

namespace App\Console\Commands;

use App\Models\DriverAreaTurn;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class RotateDriverTurns extends Command
{

    protected $signature = 'rotate:driver-turns';

    protected $description = 'تدوير الدور تلقائيًا للسائقين بعد انتهاء المهلة الزمنية';

    public function handle()
    {
        $timeoutMinutes = 5;

        // 1. جلب السائقين الذين لديهم الدور وتجاوزوا المهلة
        $expiredTurns = DriverAreaTurn::where('is_next', true)
            ->whereNotNull('turn_assigned_at')
            ->where('turn_assigned_at', '<=', Carbon::now()->subMinutes($timeoutMinutes))
            ->get();

        foreach ($expiredTurns as $currentTurn) {
            $areaId = $currentTurn->area_id;

            // 2. البحث عن السائق التالي بالدور
            $nextTurn = DriverAreaTurn::where('area_id', $areaId)
                ->where('is_active', true)
                ->where('turn_order', '>', $currentTurn->turn_order)
                ->orderBy('turn_order')
                ->first();

            // 3. في حال لم يوجد سائق لاحق، نرجع لأول سائق (دور دائري)
            if (! $nextTurn) {
                $nextTurn = DriverAreaTurn::where('area_id', $areaId)
                    ->where('is_active', true)
                    ->orderBy('turn_order')
                    ->first();
            }

            // 4. تحديث الدور
            $currentTurn->update([
                'is_next' => false,
                'turn_assigned_at' => null,
            ]);

            if ($nextTurn) {
                $nextTurn->update([
                    'is_next' => true,
                    'turn_assigned_at' => now(),
                ]);
                $this->info("تم تمرير الدور من سائق ID {$currentTurn->driver_id} إلى {$nextTurn->driver_id} في المنطقة {$areaId}");
            }
        }

        return 0;
    }
}
