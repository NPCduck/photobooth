<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventPackage extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'price',
        'photo_limit'
    ];

    public function event() {
        return $this->belongsTo(Event::class);
    }
}
