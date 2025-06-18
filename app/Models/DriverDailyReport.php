<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverDailyReport extends Model
{
    use HasFactory;
    protected $fillable = [
        'driver_id', 'date',
        'total_orders', 'total_amount',
        'driver_earnings', 'admin_earnings',
    ];
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
