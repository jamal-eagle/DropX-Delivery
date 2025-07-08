<?php

namespace App\Jobs;

use App\Models\Driver;
use App\Models\DriverAreaTurn;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateDriversTurnsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct() {}

    public function handle(): void
    {
        $now = now();
        $dayName = $now->format('l'); // Saturday, Sunday...
        $currentTime = $now->format('H:i');


        $eligibleDrivers = Driver::whereHas('workingHours', function ($query) use ($dayName, $currentTime) {
            $query->whereRaw('LOWER(day_of_week) = ?', [strtolower($dayName)])
                ->where('start_time', '<=', $currentTime)
                ->where('end_time', '>=', $currentTime);
        })
            ->with('areaTurns')
            ->get();

        $activatedCount = 0;

        foreach ($eligibleDrivers as $driver) {
            $turn = $driver->areaTurns;

            if (!$turn) {
                //Log::info("❌ السائق ID={$driver->id} لا يملك سجل في driver_area_turns");
                continue;
            }

            // Log::info("👀 سائق ID={$driver->id} - is_active = {$turn->is_active}");

            if (!$turn->is_active) {
                $turn->update(['is_active' => true]);
                // Log::info("✅ تم تفعيل الدور للسائق ID={$driver->id}");
                $activatedCount++;
            }
        }

        //Log::info("✅ تم تفعيل الدور لـ {$activatedCount} سائق/سائقين في الساعة {$now->format('H:i')}. {$dayName}");
    }
}
