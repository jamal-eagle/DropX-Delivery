<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount_type',
        'discount_value',
        'min_order_value',
        'max_uses',
        'expiry_date',
        'is_active',
    ];


    public function users()
    {
        return $this->belongsToMany(User::class, 'user_promo_codes')
            ->withPivot('is_used', 'used_at')
            ->withTimestamps();
    }

    public function orders()
    {
        return $this->belongsToMany(PromoCode::class, 'user_promo_codes')
            ->withTimestamps();
    }
}
