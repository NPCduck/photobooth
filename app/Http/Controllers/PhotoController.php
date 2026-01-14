<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    public function show($token) {
        // Logic to handle photo capture using the provided token
        $event = Event::where('public_token', $token)
            ->where('qr_active', true)
            ->firstOrFail();

        return Inertia::render('Photo/Capture', [
            'event' => $event,
        ]);
    }

    public function upload(Request $request, $token) {
        // Logic to handle photo upload using the provided token
        $event = Event::where('public_token', $token)
            ->where('qr_active', true)
            ->firstOrFail();

        $request->validate([
            'photo' => 'required|image|max:10240', // max 10MB
        ]);

        $path = "user_{$event->user_id}/event_{$event->id}/photos";
        $filename = uniqid('photo_') . '.' . $request->file('photo')->extension();

        Storage::disk('private')->putFileAs($path, $request->file('photo'), $filename);

        return response()->json(['success' => true]);
    }
}
