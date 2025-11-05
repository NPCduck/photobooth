<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PhotoController extends Controller
{
    public function capture($token) {
        // Logic to handle photo capture using the provided token
        $event = Event::where('public_token', $token)
            ->where('qr_active', true)
            ->firstOrFail();

        return Inertia::render('Photo/Capture', [
            'event' => $event,
        ]);
    }
}
