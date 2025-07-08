<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'vehicle_type',
        'vehicle_number',
        'is_active',
    ];




    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function areaTurns()
    {
        return $this->hasOne(DriverAreaTurn::class);
    }

    public function workingHours()
    {
        return $this->hasMany(DriverWorkingHour::class);
    }

    public function dailyReports()
    {
        return $this->hasMany(DriverDailyReport::class);
    }

    public function monthlyReports()
    {
        return $this->hasMany(DriverMonthlyReport::class);
    }
}
