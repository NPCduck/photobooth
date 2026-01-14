<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class ImageController extends Controller
{
    public function showPrivateImage($user_id, $event_id, $path = null, $file) {

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
}
