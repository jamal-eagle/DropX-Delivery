<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use SebastianBergmann\CodeCoverage\Driver\Driver;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'restaurant_id',
        'driver_id',
        'status',
        'is_accepted',
        'total_price',
        'delivery_address',
        'notes',
        'delivery_fee',
        'barcode',
    ];



    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function rejections()
{
    return $this->hasMany(DriverOrderRejection::class);
}




    public function updateStatusBasedOnScan()
    {
        if ($this->scanned_by === 'driver') {

            $this->status = 'on_delivery';
        } elseif ($this->scanned_by === 'user') {

            $this->status = 'delivered';
        }

        $this->save();
    }
}
