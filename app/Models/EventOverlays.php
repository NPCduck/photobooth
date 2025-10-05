<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventOverlays extends Model
{
    protected $fillable = [
        'event_id',
        'landing_img',
        'frame_img'
    ];

    public function event() {
        return $this->belongsTo(Event::class);
    }
}
