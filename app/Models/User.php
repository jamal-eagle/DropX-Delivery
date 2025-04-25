<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Driver;


class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes,HasApiTokens;

    protected $fillable = [
        'fullname',
        'phone',
        'password',
        'location_text',
        'latitude',
        'longitude',
        'is_active',
        'fcm_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'is_active',
        'user_type',
        'fcm_token',
        'deleted_at',
        'created_at',
        'updated_at'

    ];

    protected $casts = [
        'password' => 'hashed',
        'is_active' => 'boolean',
        'user_type' => 'string',
        'email_verified_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];





    public function restaurant()
    {
        return $this->hasOne(restaurant::class);
    }

    public function driver()
    {
        return $this->hasOne(Driver::class);
    }

    public function promoCodes()
    {
        return $this->belongsToMany(PromoCode::class, 'user_promo_codes')
            ->withPivot('is_used', 'used_at')
            ->withTimestamps();
    }
    public function areas()
    {
        return $this->belongsToMany(Area::class, 'area_user')->withTimestamps();
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
