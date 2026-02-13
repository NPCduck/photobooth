<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventOverlays extends Model
{

    use HasFactory;

    protected $fillable = [
        'event_id',
        'landing_img',
        'frame_img',
        'frame_position',
        'frame_stretch'
    ];

    public function event() {
        return $this->belongsTo(Event::class);
    }
}
