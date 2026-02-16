<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'user_id',
        'event_id',
        'guest_id',
        'code',
        'amount',
        'status',
        'is_test',
        'payment_gateway',
        'payment_reference',
        'cancelled_reason',
        'cancelled_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function guest()
    {
        return $this->belongsTo(EventGuest::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /* Helpers */

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
