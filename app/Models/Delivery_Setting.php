<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery_Setting extends Model
{
    use HasFactory;
    protected $fillable = [
        'price_per_km',
        'minimum_delivery_fee',
    ];
}
