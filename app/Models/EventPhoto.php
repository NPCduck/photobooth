<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventPhoto extends Model
{
    protected $fillable = [
        'event_id',
        'event_guest_id',
        'path'
    ];

    public function event() {
        return $this->belongsTo(Event::class);
    }

    public function guest() {
        return $this->belongsTo(EventGuest::class, 'event_guest_id');
    }
}
