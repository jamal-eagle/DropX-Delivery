<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantMonthlyReport extends Model
{
    use HasFactory;
    protected $fillable = [
        'restaurant_id',
        'month_date',
        'total_orders',
        'total_amount',
        'commission_type',
        'commission_value',
        'system_earnings',
        'restaurant_earnings',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
