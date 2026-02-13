<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'package_id',
        'name',
        'unit_price',
        'quantity',
        'total_price',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function package()
    {
        return $this->belongsTo(EventPackage::class);
    }
}
