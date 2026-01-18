<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class Event extends Model
{

    use HasFactory;

    protected $fillable = [
        'user_id',
        'name'
    ];

    protected static function booted() {
        static::creating(function ($event) {
            if (empty($event->public_token)) {
                $event->public_token = Str::uuid();
            }
        });

        static::created(function ($event) {
            $url = route('capture.show', $event->public_token);
            
            $qrSvg = QrCode::format('svg')
                ->size(300)
                ->errorCorrection('H')
                ->generate($url);

            Storage::disk('private')
                ->put('user_' . $event->user_id . '/event_' . $event->id . '/qr.svg', $qrSvg);
        });
    }

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

    public function guests() {
        return $this->hasMany(EventGuest::class);
    }

    public function photos() {
        return $this->hasMany(EventPhoto::class);
    }
}
