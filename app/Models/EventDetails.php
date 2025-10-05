<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventDetails extends Model
{
    protected $fillable = [
        'event_id',
        'type',
        'date',
        'time_start',
        'time_end',
        'loc_venue',
        'loc_address'
    ];

    public function event() {
        return $this->belongsTo(Event::class);
    }
}
