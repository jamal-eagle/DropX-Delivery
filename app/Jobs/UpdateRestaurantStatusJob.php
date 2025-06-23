<?php

namespace App\Jobs;

use App\Models\Restaurant;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateRestaurantStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct() {}


    public function handle(): void
    {
        $now = Carbon::now()->format('H:i');

        $opened = Restaurant::where('working_hours_start', '<=', $now)
            ->where('status', '!=', 'open')
            ->update(['status' => 'open']);


        $closed = Restaurant::where('working_hours_end', '<=', $now)
            ->where('status', '!=', 'closed')
            ->update(['status' => 'closed']);


    }
}
