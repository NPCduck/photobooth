<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

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
            $url = route('events.show', $event->id);
            $qr = QrCode::format('png')
                ->size(300)
                ->errorCorrection('H')
                ->style('square')
                ->eye('circle')
                ->generate($url, null, 'gd');

            Storage::disk('private')->put('user_' . $event->user_id . '/event_' . $event->id . '/qr.png', $qr);
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
}
