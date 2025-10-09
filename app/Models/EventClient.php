<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventClient extends Model
{

    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'email',
        'phone'
    ];

    function event(Event $event) {
        return $this->belongsTo(Event::class);
    }
}
