<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use App\Models\Event;

class ImageController extends Controller
{
    public function showPrivateImage($user_id, $event_id, $path = null, $file) {
        // 🔴 SECURITY: Autorizácia - musí byť vlastník
        if ((int)$user_id !== auth()->id()) {
            abort(403, 'Nemáte oprávnenie pristupovať k tomuto súboru');
        }

        // 🔴 SECURITY: Kontrola event ownership
        $event = Event::where('id', $event_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $fullPath = "user_{$user_id}/event_{$event_id}/{$path}";

        $files = Storage::disk('private')->files($fullPath);
        
        $fileMatch = collect($files)->first(function($f) use ($file) {
            $name = pathinfo($f, PATHINFO_FILENAME);
            $ext = pathinfo($f, PATHINFO_EXTENSION);
            return $name === $file && in_array($ext, ['jpg','jpeg','png','webp']);
        });

        if (!$fileMatch) {
            abort(404, 'Súbor sa nenašiel!');
        }

        $content = Storage::disk('private')->get($fileMatch);
        $mime = Storage::disk('private')->mimeType($fileMatch);

        return response($content, 200)
            ->header('Content-Type', $mime)
            ->header('Content-Disposition', 'inline');
    }

    public function showQrCode($user_id, $event_id, $file) {
        // 🔴 SECURITY: Autorizácia - musí byť vlastník
        if ((int)$user_id !== auth()->id()) {
            abort(403, 'Nemáte oprávnenie pristupovať k tomuto súboru');
        }

        // 🔴 SECURITY: Kontrola event ownership
        $event = Event::where('id', $event_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $fullPath = "user_{$user_id}/event_{$event_id}";

        $files = Storage::disk('private')->files($fullPath);

        $fileMatch = collect($files)->first(function($f) use ($file) {
            return str_starts_with(pathinfo($f, PATHINFO_FILENAME), $file)
                && in_array(pathinfo($f, PATHINFO_EXTENSION), ['jpg','jpeg','png','webp', 'svg']);
        });

        if (!$fileMatch) {
            abort(404, 'Súbor sa nenašiel!');
        }

        $content = Storage::disk('private')->get($fileMatch);
        $mime = Storage::disk('private')->mimeType($fileMatch);

        return response($content, 200)
            ->header('Content-Type', $mime)
            ->header('Content-Disposition', 'inline');
    }

    function showSvgFrame($user_id, $event_id, $path, $file) {
        // 🔴 SECURITY: Autorizácia - musí byť vlastník
        if ((int)$user_id !== auth()->id()) {
            abort(403, 'Nemáte oprávnenie pristupovať k tomuto súboru');
        }

        $event = Event::where('id', $event_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // 🔴 SECURITY: Kontrola ownership
        if ($event->user_id !== auth()->id()) {
            abort(403, 'Nemáte oprávnění přistupovat k tomuto souboru');
        }

        $fullPath = "user_{$user_id}/event_{$event_id}/{$path}/{$file}";

        if (!Storage::disk('private')->exists($fullPath)) {
            abort(404, 'Soubor nenalezen!');
        }

        

        $content = Storage::disk('private')->get($fullPath);
        $mime = Storage::disk('private')->mimeType($fullPath);

        return response($content, 200)
            ->header('Content-Type', $mime)
            ->header('Content-Disposition', 'inline');
    }
}
