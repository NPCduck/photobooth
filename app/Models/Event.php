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
        return $this->hasOne(EventDetails::class);
    }

    public function packages() {
        return $this->hasMany(EventPackages::class);
    }

    public function overlays() {
        return $this->hasOne(EventOverlays::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function orders() {
        return $this->hasMany(Order::class);
    }
}
