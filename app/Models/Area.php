<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;
    protected $fillable = [
        'city',
        'neighborhood',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'area_user')
            ->withTimestamps();
    }

    public function driverTurns()
    {
        return $this->hasMany(DriverAreaTurn::class);
    }
}
