<?php

namespace App\Jobs;

use App\Models\Restaurant;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateRestaurantStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(string $mode = 'open')
    {
        $this->mode = $mode;
    }


    public function handle(): void
    {
        $now = Carbon::now()->format('H:i');

        if ($this->mode === 'open') {
            Restaurant::where('working_hours_start', '<=', $now)
                ->update(['status' => 'open']);
        } elseif ($this->mode === 'close') {
            Restaurant::where('working_hours_end', '<=', $now)
                ->update(['status' => 'closed']);
        }
    }
}
