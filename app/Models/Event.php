<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'user_id',
        'name'
    ];

    protected $with = ['details', 'packages', 'overlays'];

    public function details() {
        return $this->hasOne(EventDetail::class);
    }

    public function packages() {
        return $this->hasMany(EventPackage::class);
    }

    public function overlays() {
        return $this->hasOne(EventOverlay::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
