<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\Event;
use App\Models\EventGuest;
use App\Models\EventPhoto;
use App\Models\Action;
use App\Models\Order;
use App\Models\EventPackage;

use Inertia\Inertia;

use App\Http\Controllers\OrderController;

class PhotoController extends Controller
{
    public function show($token) {
        // Logic to handle photo capture using the provided token
        $event = Event::where('public_token', $token)
            ->where('qr_active', true)
            ->with('overlays')
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
            'photo' => [
                'required',
                'image',
                'mimetypes:image/jpeg,image/png,image/webp',
                'max:10240',
            ],
            'guest_id' => 'required|integer|exists:event_guests,id',
        ]);

        // 🔴 SECURITY: Hosť MUSÍ patriť k tomuto eventu
        $guest = EventGuest::where('id', $request->guest_id)
            ->where('event_id', $event->id)
            ->with('package')
            ->firstOrFail();

        return DB::transaction(function () use ($request, $event, $guest) {

            // 🔴 SECURITY: Zamkneme riadok hosťa (race condition protection)
            $guest = EventGuest::where('id', $guest->id)
                ->lockForUpdate()
                ->with('package')
                ->first();

            if (!$guest) {
                abort(404, 'Hosť nenájdený');
            }

            $limit = $guest->package?->photo_limit_person;

            // 🔴 SECURITY: Ak má balík limit a je dosiahnutý
            if ($limit !== null && $guest->photos_uploaded >= $limit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dosiahnutý limit fotiek.',
                ], 403);
            }

            // 🔴 SECURITY: Zamkneme aj event
            $event = Event::where('id', $event->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Uloženie fotky - bezpečný názov súboru
            $path = "user_{$event->user_id}/event_{$event->id}/photos";
            $filename = uniqid('photo_', true) . '.jpg';

            Storage::disk('private')->putFileAs(
                $path,
                $request->file('photo'),
                $filename
            );

            // DB záznam fotky
            EventPhoto::create([
                'event_id'       => $event->id,
                'event_guest_id' => $guest->id,
                'path'           => "$path/$filename",
            ]);

            // Audit log
            Action::create([
                'user_id'     => $event->user_id,
                'event_id'    => $event->id,
                'guest_id'    => $guest->id,
                'action_type' => 'Nahratie fotky',
                'description' => "Hosť {$guest->email} nahral fotku.",
            ]);

            // 🔴 SECURITY: Zvýšenie počtu nahratých fotiek v transakácii
            $guest->photos_uploaded++;
            $guest->save();

            return response()->json([
                'success'  => true,
                'redirect' => route('capture.thankYou', $event->public_token),
            ]);
        }, attempts: 3);  // Retry na konflikt
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

        //guest neexistuje → treba vybrať balíček
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

        //prekročený limit
        if ($uploadedCount >= $limit) {
            return response()->json([
                'exists' => true,
                'allowed' => false,
                'message' => 'Dosiahnutý limit fotiek pre tento balíček.',
            ], 403);
        }

        //môže pokračovať
        return response()->json([
            'exists' => true,
            'allowed' => true,
            'guest_id' => $guest->id,
            'remaining' => $limit - $uploadedCount,
        ]);
    }

    public function createGuest(Request $request, $token) {
        $event = Event::where('public_token', $token)
            ->where('qr_active', true)
            ->firstOrFail();

        $request->validate([
            'email' => 'required|email',
            'package_id' => 'required|exists:event_packages,id',
        ]);

        // 🔴 SECURITY: Balík MUSÍ patriť k tomuto eventu!
        $package = EventPackage::where('id', $request->package_id)
            ->where('event_id', $event->id)
            ->firstOrFail();
        
        $photo_limit = $package->photo_limit_person;

        return DB::transaction(function () use ($request, $event, $package, $photo_limit) {

            $guest = EventGuest::create([
                'event_id' => $event->id,
                'email' => $request->email,
                'package_id' => $package->id,
                'photo_limit' => $photo_limit,
            ]);

            // ⬇️ delegujeme tvorbu objednávky
            app(OrderController::class)->storeForGuestInternal(
                $guest,
                $event,
                $package->id
            );

            return response()->json([
                'success' => true,
                'guest_id' => $guest->id,
            ]);
        });
    }

    public function getPhotoUrl($path) {
        // 🔴 SECURITY: Extrahovanie a verifikácia user_id z path
        $decodedPath = urldecode($path);

        if (!Storage::disk('private')->exists($decodedPath)) {
            abort(404);
        }

        // Extrahovanie user_id z path: user_ID/event_ID/...
        if (!preg_match('/^user_(\d+)\//', $decodedPath, $matches)) {
            abort(404);
        }

        $userId = (int) $matches[1];

        // 🔴 SECURITY: Autorizácia - môže vidieť len obrázky svojich eventov
        if ($userId !== auth()->id()) {
            abort(403);
        }

        // 🔴 SECURITY: Extrahovanie event_id a kontrola ownership
        if (!preg_match('/^user_\d+\/event_(\d+)\//', $decodedPath, $eventMatches)) {
            abort(404);
        }

        $eventId = (int) $eventMatches[1];

        // Verifikácia, že event patrí danému userovi
        Event::where('id', $eventId)
            ->where('user_id', $userId)
            ->firstOrFail();

        return response()->file(Storage::disk('private')->path($decodedPath));
    }
}
