<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventGuest extends Model
{
    protected $fillable = [
        'event_id',
        'email',
        'package_id',
        'photo_limit',
        'photos_uploaded'
    ];

    public function event() {
        return $this->belongsTo(Event::class);
    }

    public function package() {
        return $this->belongsTo(EventPackage::class, 'package_id');
    }

    public function photos() {
        return $this->hasMany(EventPhoto::class, 'event_guest_id');
    }

    public function canUpload() : bool {
        return $this->photos_uploaded < $this->photo_limit;
    }
}
