<?php

namespace App\Console\Commands;

use App\Models\DriverAreaTurn;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class RotateDriverTurns extends Command
{

    protected $signature = 'rotate:driver-turns';

    protected $description = 'تدوير الدور تلقائيًا للسائقين بعد انتهاء المهلة الزمنية';

    public function handle()
    {
        $timeoutMinutes = 10;

        $expiredTurns = DriverAreaTurn::where('is_next', true)
            ->whereNotNull('turn_assigned_at')
            ->where('turn_assigned_at', '<=', Carbon::now()->subMinutes($timeoutMinutes))
            ->get();

        foreach ($expiredTurns as $currentTurn) {
            $areaId = $currentTurn->area_id;

            $candidates = DriverAreaTurn::where('area_id', $areaId)
                ->where('is_active', true)
                ->where('id', '!=', $currentTurn->id)
                ->orderBy('turn_order')
                ->get();

            $nextTurn = null;

            foreach ($candidates as $candidate) {
                $hasActiveOrder = Order::where('driver_id', $candidate->driver_id)
                    ->where('status', 'on_delivery')
                    ->exists();

                if (! $hasActiveOrder) {
                    $nextTurn = $candidate;
                    break;
                }
            }

            $currentTurn->update([
                'is_next' => false,
                'turn_assigned_at' => null,
            ]);

            if ($nextTurn) {
                $nextTurn->update([
                    'is_next' => true,
                    'turn_assigned_at' => now(),
                ]);

                $this->info("✅ تم تمرير الدور من سائق ID {$currentTurn->driver_id} إلى {$nextTurn->driver_id} في المنطقة {$areaId}");
            } else {
                $currentTurn->update([
                    'is_next' => true,
                    'turn_assigned_at' => now(),
                ]);

                $this->info("⚠️ لم يوجد سائق متاح، تم إبقاء الدور عند السائق ID {$currentTurn->driver_id} في المنطقة {$areaId}");
            }
        }

        return 0;
    }
}
