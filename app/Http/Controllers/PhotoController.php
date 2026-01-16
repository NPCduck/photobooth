<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventGuest;
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
        $event = Event::where('public_token', $token)
            ->where('qr_active', true)
            ->firstOrFail();

        $request->validate([
            'photo' => 'required|image|max:10240',
            'guest_id' => 'required|exists:event_guests,id',
        ]);

        $guest = EventGuest::findOrFail($request->guest_id);

        // kontrola limitu ešte pred uploadom
        $uploadedCount = $guest->photos()->count();
        $limit = $guest->package?->photo_limit_person ?? 0;

        if ($uploadedCount >= $limit) {
            return response()->json([
                'success' => false,
                'message' => 'Dosiahnutý limit fotiek pre tento balíček.',
            ], 403);
        }

        $path = "user_{$event->user_id}/event_{$event->id}/photos";
        $filename = uniqid('photo_') . '.' . $request->file('photo')->extension();

        Storage::disk('private')->putFileAs($path, $request->file('photo'), $filename);

        EventPhoto::create([
            'event_id' => $event->id,
            'event_guest_id' => $guest->id,
            'path' => "$path/$filename",
        ]);

        return response()->json(['success' => true]);
    }

    public function checkEmail(Request $request, $token) {
        $event = Event::where('public_token', $token)
            ->where('qr_active', true)
            ->firstOrFail();

        $request->validate([
            'email' => 'required|email',
        ]);

        $guest = EventGuest::where('event_id', $event->id)
            ->where('email', $request->email)
            ->first();

        // ❌ guest neexistuje → treba vybrať balíček
        if (!$guest) {
            return response()->json([
                'exists' => false,
                'packages' => $event->packages()->get([
                    'id',
                    'name',
                    'price',
                    'photo_limit_person'
                ]),
            ]);
        }

        $uploadedCount = $guest->photos()->count();
        $limit = $guest->package?->photo_limit_person ?? 0;

        // ⚠️ prekročený limit
        if ($uploadedCount >= $limit) {
            return response()->json([
                'exists' => true,
                'allowed' => false,
                'message' => 'Dosiahnutý limit fotiek pre tento balíček.',
            ], 403);
        }

        // ✅ môže pokračovať
        return response()->json([
            'exists' => true,
            'allowed' => true,
            'guest_id' => $guest->id,
            'remaining' => $limit - $uploadedCount,
        ]);
    }

    function createGuest(Request $request, $token) {
        $event = Event::where('public_token', $token)
            ->where('qr_active', true)
            ->firstOrFail();

        $request->validate([
            'email' => 'required|email',
            'package_id' => 'required|exists:event_packages,id',
        ]);

        // skontrolovať, či už neexistuje
        $existingGuest = EventGuest::where('event_id', $event->id)
            ->where('email', $request->email)
            ->first();

        if ($existingGuest) {
            return response()->json([
                'success' => false,
                'message' => 'Host už existuje. Prosím, skontrolujte email.',
            ], 409);
        }

        $guest = EventGuest::create([
            'event_id' => $event->id,
            'email' => $request->email,
            'package_id' => $request->package_id,
        ]);

        return response()->json([
            'success' => true,
            'guest_id' => $guest->id,
        ]);
    }

}
