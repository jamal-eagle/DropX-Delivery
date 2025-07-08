<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverMonthlyReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'month_date',
        'total_orders',
        'total_delivery_fees',
        'driver_earnings',
        'admin_earnings',
    ];
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
