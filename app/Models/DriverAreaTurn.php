<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverAreaTurn extends Model
{
    use HasFactory;
    protected $fillable = [
        'driver_id', 'area_id', 'turn_order', 'is_next', 'is_active', 'last_assigned_at'
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

}
