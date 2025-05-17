<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'image',
        'description',
        'working_hours_start',
        'working_hours_end',
        'status',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function commission()
    {
        return $this->hasOne(RestaurantCommission::class);
    }
    public function dailyReports()
    {
        return $this->hasMany(RestaurantDailyReport::class);
    }



    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }
    public function meals()
    {
        return $this->hasMany(Meal::class);
    }
}
