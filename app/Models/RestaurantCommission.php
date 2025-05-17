<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'type',
        'value',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
