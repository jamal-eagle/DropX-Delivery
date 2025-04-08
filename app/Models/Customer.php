<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'middle_name',
        'last_name',
        'location_text',
        'latitude',
        'longitude',
    ];



    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
    // public function orders()
    // {
    //     return $this->hasMany(Order::class);
    // }
}
