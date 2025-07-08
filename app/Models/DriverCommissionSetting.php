<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverCommissionSetting extends Model
{
    use HasFactory;
    protected $fillable = [
        'driver_percentage',
        'type',
    ];
}
