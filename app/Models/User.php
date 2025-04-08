<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use SebastianBergmann\CodeCoverage\Driver\Driver;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'phone',
        'password',
        'user_type',
        'is_active',
        'profile_image',
        'fcm_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'is_active' => 'boolean',
        'user_type' => 'string',
        'email_verified_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];



    public function customer()
    {
        return $this->hasOne(customer::class);
    }

    public function restaurant()
    {
        return $this->hasOne(restaurant::class);
    }

    public function driver()
    {
        return $this->hasOne(Driver::class);
    }

    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    public function promoCodes()
    {
        return $this->belongsToMany(PromoCode::class, 'user_promo_codes')
            ->withPivot('is_used', 'used_at')
            ->withTimestamps();
    }

    // public function orders()
    // {
    //     return $this->hasMany(Order::class);
    // }
}
