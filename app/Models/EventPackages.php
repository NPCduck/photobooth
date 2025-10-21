<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventPackages extends Model
{

    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'price',
        'photo_limit_total',
        'photo_limit_person',
    ];

    public function event() {
        return $this->belongsTo(Event::class);
    }
}
