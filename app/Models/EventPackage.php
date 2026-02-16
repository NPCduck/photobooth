<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventPackage extends Model
{

    use HasFactory, SoftDeletes;

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
