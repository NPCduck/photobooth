<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventDetails extends Model
{

    use HasFactory;

    protected $fillable = [
        'event_id',
        'type',
        'hosts',
        'status',
        'hosts',
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
