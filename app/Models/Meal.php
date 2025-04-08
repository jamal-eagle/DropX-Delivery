<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'original_price',
        'is_available',
    ];


    public function category()
    {
        return $this->belongsTo(Category::class);
    }


    public function images()
    {
        return $this->hasMany(ImageForMeal::class);
    }


    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
