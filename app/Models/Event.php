<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{

    use HasFactory;

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

    public function actions() {
        return $this->hasMany(Action::class);
    }

    public function client() {
        return $this->hasOne(EventClient::class);
    }
}
