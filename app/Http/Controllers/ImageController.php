<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class ImageController extends Controller
{
    public function showPrivateImage($user_id, $event_id, $path, $file) {
        $path = "user_{$user_id}/event_{$event_id}/{$path}";

        $files = Storage::disk('private')->files($path);
        $fileMatch = collect($files)->first(fn($f) => str_contains($f, $file));

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
