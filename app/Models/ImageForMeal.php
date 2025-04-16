<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageForMeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'meal_id',
        'image',
        'description',
    ];

    public function meal()
    {
        return $this->belongsTo(Meal::class,'meal_id');
    }
}
