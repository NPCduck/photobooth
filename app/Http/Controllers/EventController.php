<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Event;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request) {
        $events = auth()->user()
            ->events()
            ->with(['details', 'packages', 'overlays', 'orders'])
            ->search($request->search)
            ->status($request->status)
            ->sortBy($request->sort)
            ->get();

        return Inertia::render('Events/Index', [
            'events' => $events,
            'filters' => $request->only(['search', 'status', 'sort']),
        ]);
    }

    public function show(Event $event) {
        $this->authorize('view', $event);
        $event->load(['details', 'packages', 'overlays', 'orders', 'actions', 'client']);
        $qrurl = route('capture.show', $event->public_token);

        return Inertia::render('Events/Show', [
            'event' => $event,
            'qrurl' => $qrurl,
        ]);
    }

    public function create() {
        return Inertia::render('Events/Create');
    }

    public function store(Request $request) {
        // 🔴 SECURITY: Rozšírená validácia s limity
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'details.type' => 'required|string|max:255',
            'details.hosts' => [
                'required',
                'integer',
                'min:1',
                'max:10000',  // 🔴 Rozumný limit
            ],
            'details.status' => 'required|string|max:255',
            'details.date' => 'required|date',
            'details.time_start' => 'required|date_format:H:i',
            'details.time_end' => 'nullable|date_format:H:i',
            'details.loc_venue' => 'required|string|max:255',
            'details.loc_address' => 'required|string|max:255',
            'packages.*.name' => 'required|string|max:255',
            'packages.*.price' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',  // 🔴 Maximálna cena
            ],
            'packages.*.photo_limit_total' => [
                'required',
                'integer',
                'min:0',
                'max:10000',  // 🔴 Rozumný limit
            ],
            'packages.*.photo_limit_person' => [
                'required',
                'integer',
                'min:0',
                'max:1000',  // 🔴 Maximálne fotky na osobu
            ],
            'client.name' => 'required|string|max:255',
            'client.email' => 'required|email|max:255',
            'client.phone' => [
                'required',
                'string',
                'regex:/^[0-9+\-\s()]+$/',  // 🔴 Iba telefónne čísla
                'min:10',
                'max:15',
            ],
            'overlays.landing_img' => 'nullable|file|mimetypes:image/jpeg,image/png,image/webp|max:4096',
            'overlays.frame_img' => 'nullable|file|mimetypes:image/jpeg,image/png,image/webp|max:4096',
        ]);

        $event = auth()->user()->events()->create([
            'name' => $data['name'],
        ]);

        $event->details()->create($data['details']);

        $event->client()->create($data['client']);

        foreach ($data['packages'] as $package) {
            $event->packages()->create($package);
        }

        if (!empty($data['overlays'])) {
            $landing = false;
            $frame = false;
            $user_id = auth()->id();

            if (!empty($data['overlays']['landing_img'])) {
                $file = $data['overlays']['landing_img'];

                // 🔴 SECURITY: Validuj extension
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                $extension = strtolower($file->getClientOriginalExtension());
                if (!in_array($extension, $allowedExtensions)) {
                    return back()->withErrors(['overlays.landing_img' => 'Neplatný typ súboru']);
                }

                $path = $file->storeAs(
                    'user_' . $user_id . '/event_' . $event->id . '/overlays',
                    'landing_img.'.$extension,
                    'private');
                $landing = true;
            }

            if (!empty($data['overlays']['frame_img'])) {
                $file = $data['overlays']['frame_img'];

                // 🔴 SECURITY: Validuj extension
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                $extension = strtolower($file->getClientOriginalExtension());
                if (!in_array($extension, $allowedExtensions)) {
                    return back()->withErrors(['overlays.frame_img' => 'Neplatný typ súboru']);
                }

                $path = $file->storeAs(
                    'user_' . $user_id . '/event_' . $event->id . '/overlays',
                    'frame_img.'.$extension,
                    'private');
                $frame = true;
            }

            $event->overlays()->create([
                'landing_img' => $landing,
                'frame_img' => $frame,
            ]);
        }

        return redirect()->route('events.show', $event);
    }

    public function edit(Event $event) {
        $this->authorize('update', $event);

        $event->load(['details', 'packages', 'overlays', 'client']);

        return Inertia::render('Events/Edit', [
            'event' => $event,
        ]);
    }

    public function update(Request $request, Event $event) {
        $this->authorize('update', $event);

        // 🔴 SECURITY: Rozšírená validácia s limity
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'details.type' => 'required|string|max:255',
            'details.hosts' => [
                'required',
                'integer',
                'min:1',
                'max:10000',
            ],
            'details.status' => 'required|string|max:255',
            'details.date' => 'required|date',
            'details.time_start' => 'required|date_format:H:i',
            'details.time_end' => 'nullable|date_format:H:i',
            'details.loc_venue' => 'required|string|max:255',
            'details.loc_address' => 'required|string|max:255',
            'packages' => 'required|array',
            'packages.*.id' => 'nullable|exists:event_packages,id',
            'packages.*.name' => 'required|string|max:255',
            'packages.*.price' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
            ],
            'packages.*.photo_limit_total' => [
                'required',
                'integer',
                'min:0',
                'max:10000',
            ],
            'packages.*.photo_limit_person' => [
                'required',
                'integer',
                'min:0',
                'max:1000',
            ],
            'client.name' => 'required|string|max:255',
            'client.email' => 'required|email|max:255',
            'client.phone' => [
                'required',
                'string',
                'regex:/^[0-9+\-\s()]+$/',
                'min:10',
                'max:15',
            ],
            'overlays.file_landing' => 'nullable|file|mimetypes:image/jpeg,image/png,image/webp|max:4096',
            'overlays.file_frame' => 'nullable|file|mimetypes:image/jpeg,image/png,image/webp|max:4096',
            'overlays.frame_position' => 'nullable|string|in:stretch,top-left,top-right,bottom-left,bottom-right,center',
            'overlays.frame_stretch' => 'nullable|boolean',
        ]);

        // 🔴 SECURITY: Validácia existujúcich balíčkov
        foreach ($data['packages'] as $package) {
            if (isset($package['id'])) {
                // Kontrola, že balík patrí k tomuto eventu!
                \App\Models\EventPackage::where('id', $package['id'])
                    ->where('event_id', $event->id)
                    ->firstOrFail();
            }
        }

        $event->update([
            'name' => $data['name'],
        ]);

        $event->client()->update($data['client']);

        $event->details()->update($data['details']);

        // Packages - synchronizácia existujúcich a nových
        $existingPackageIds = $event->packages()->pluck('id')->toArray();
        $submittedPackageIds = collect($data['packages'])->pluck('id')->filter()->toArray();

        // Odstránenie neexistujúcich
        $packagesToDelete = array_diff($existingPackageIds, $submittedPackageIds);
        if (!empty($packagesToDelete)) {
            $event->packages()->whereIn('id', $packagesToDelete)->delete();
        }

        // Update or create packages
        foreach ($data['packages'] as $package) {
            if (isset($package['id'])) {
                // Update existing package
                $event->packages()->where('id', $package['id'])->update($package);
            } else {
                // Create new package
                $event->packages()->create($package);
            }
        }

        if (!empty($data['overlays'])) {
            $landing = false;
            $frame = false;
            $user_id = auth()->id();

            if (!empty($data['overlays']['file_landing'])) {
                $file = $data['overlays']['file_landing'];

                // 🔴 SECURITY: Validuj extension
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                $extension = strtolower($file->getClientOriginalExtension());
                if (!in_array($extension, $allowedExtensions)) {
                    return back()->withErrors(['overlays.file_landing' => 'Neplatný typ súboru']);
                }

                $path = $file->storeAs(
                    'user_' . $user_id . '/event_' . $event->id . '/overlays',
                    'landing_img.'.$extension,
                    'private');
                $landing = true;
            }

            if (!empty($data['overlays']['file_frame'])) {
                $file = $data['overlays']['file_frame'];

                // 🔴 SECURITY: Validuj extension
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                $extension = strtolower($file->getClientOriginalExtension());
                if (!in_array($extension, $allowedExtensions)) {
                    return back()->withErrors(['overlays.file_frame' => 'Neplatný typ súboru']);
                }

                $path = $file->storeAs(
                    'user_' . $user_id . '/event_' . $event->id . '/overlays',
                    'frame_img.'.$extension,
                    'private');
                $frame = true;
            }

            $overlay_data = [];
            $overlay_data += $landing ? ['landing_img' => $landing] : [];
            $overlay_data += $frame   ? ['frame_img'   => $frame]   : [];

            if (!empty($data['overlays']['frame_position'])) {
                $overlay_data['frame_position'] = $data['overlays']['frame_position'];
            }
            if (isset($data['overlays']['frame_stretch'])) {
                $overlay_data['frame_stretch'] = $data['overlays']['frame_stretch'];
            }

            $event->overlays()->updateOrCreate(
                ['event_id' => $event->id,],
                $overlay_data
            );
        } else {
            // Ak nie sú nové obrázky ale sú overlay settings, aktualizuj ich
            if (!empty($data['overlays']['frame_position']) || isset($data['overlays']['frame_stretch'])) {
                $overlay_data = [];
                if (!empty($data['overlays']['frame_position'])) {
                    $overlay_data['frame_position'] = $data['overlays']['frame_position'];
                }
                if (isset($data['overlays']['frame_stretch'])) {
                    $overlay_data['frame_stretch'] = $data['overlays']['frame_stretch'];
                }
                $event->overlays()->update($overlay_data);
            }
        }

        return redirect()->route('events.show', $event);
    }

    public function destroy(Event $event) {
        \Log::info('Destroy called for event ID: ' . $event->id);
        $this->authorize('delete', $event);
        $rmf_status = true;

        $user_id = auth()->id();
        $event_path = 'user_' . $user_id . '/event_' . $event->id;

        if (Storage::disk('private')->exists($event_path)) {
            if (!Storage::disk('private')->deleteDirectory($event_path)) {
                $rmf_status = false;
            }
        }

        if ($rmf_status) {
            $event->delete();
        } else {
            return Inertia::render('Events/Show', [
                'event' => $event,
                'rmf_error' => 'Pri zmazaní eventu došlo ku chybe!',
            ]);
        }

        return redirect()->route('events.index');
    }

    public function photos(Event $event) {
        $this->authorize('view', $event);

        $event->load('photos.guest');

        return Inertia::render('Events/Photos', [
            'event' => $event,
        ]);
    }

    public function ordersIndex(Event $event) {
        $this->authorize('view', $event);

        $event->load(['orders.guest', 'orders.items']);

        return Inertia::render('Events/Orders/Index', [
            'event' => $event,
        ]);
    }


    //SVG FRAME

    function uploadFrameSvg(Request $request, Event $event) {
        $this->authorize('update', $event);

        $data = $request->validate([
            'frame_svg' => 'required|file|mimetypes:image/svg+xml|max:2048',
        ]);

        $file = $data['frame_svg'];
        $user_id = auth()->id();

        // 🔴 SECURITY: Validuj extension
        if ($file->getClientOriginalExtension() !== 'svg') {
            return back()->withErrors(['frame_svg' => 'Neplatný typ souboru']);
        }

        Storage::disk('private')->putFileAs(
            "user_{$user_id}/event_{$event->id}/overlays",
            $file,
            'frame.svg'
        );

        // Aktualizuj overlay záznam
        $event->overlays()->updateOrCreate(
            ['event_id' => $event->id],
            ['frame_svg' => true]  // Označíme, že máme frame svg
        );

        return back()->with('success', 'SVG rám byl úspěšně nahrán!');
    }

    function deleteFrameSvg(Event $event) {
        $this->authorize('update', $event);

        $user_id = $event->user_id;
        $path = "user_{$user_id}/event_{$event->id}/overlays/frame.svg";

        DB::transaction(function () use ($event, $path) {
            // Odstráň soubor, pokud existuje
             if (Storage::disk('private')->exists($path)) {
                Storage::disk('private')->delete($path);
            }

            // Aktualizuj overlay záznam
            $event->overlays()->update(
                ['frame_svg' => false]  // Označíme, že nemáme frame svg
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'SVG rám bol úspešne odstránený!'
        ]);
    }

    function uploadLandingImg(Request $request, Event $event) {
        $this->authorize('update', $event);

        $data = $request->validate([
            'landing_img' => 'required|file|mimetypes:image/jpeg,image/png,image/webp|max:4096',
        ]);

        $file = $data['landing_img'];
        $user_id = auth()->id();

        // 🔴 SECURITY: Validuj extension
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $allowedExtensions)) {
            return back()->withErrors(['landing_img' => 'Neplatný typ souboru']);
        }

        Storage::disk('private')->putFileAs(
            "user_{$user_id}/event_{$event->id}/overlays",
            $file,
            'landing_img.'.$extension
        );

        // Aktualizuj overlay záznam
        $event->overlays()->updateOrCreate(
            ['event_id' => $event->id],
            ['landing_img' => true]  // Označíme, že máme landing img
        );

        return back()->with('success', 'Landing image byl úspěšně nahrán!');
    }

    function deleteLandingImg(Event $event) {
        $this->authorize('update', $event);

        $user_id = $event->user_id;
        $pathPattern = "user_{$user_id}/event_{$event->id}/overlays/landing_img.*";

        // Najdi existující landing image
        $files = Storage::disk('private')->files("user_{$user_id}/event_{$event->id}/overlays");
        $landingFile = collect($files)->first(function ($file) use ($pathPattern) {
            return fnmatch($pathPattern, $file);
        });

        if ($landingFile) {
            Storage::disk('private')->delete($landingFile);
        }

        // Aktualizuj overlay záznam
        $event->overlays()->update(
            ['landing_img' => false]  // Označíme, že nemáme landing img
        );

        return response()->json([
            'success' => true,
            'message' => 'Landing image byl úspěšně odstraněn!'
        ]);
    }

    function uploadFrameImg(Request $request, Event $event) {
        $this->authorize('update', $event);

        $data = $request->validate([
            'frame_img' => 'required|file|mimetypes:image/jpeg,image/png,image/webp|max:4096',
        ]);

        $file = $data['frame_img'];
        $user_id = auth()->id();

        // 🔴 SECURITY: Validuj extension
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $allowedExtensions)) {
            return back()->withErrors(['frame_img' => 'Neplatný typ souboru']);
        }

        Storage::disk('private')->putFileAs(
            "user_{$user_id}/event_{$event->id}/overlays",
            $file,
            'frame_img.'.$extension
        );

        // Aktualizuj overlay záznam
        $event->overlays()->updateOrCreate(
            ['event_id' => $event->id],
            ['frame_img' => true]  // Označíme, že máme frame img
        );

        return back()->with('success', 'Overlay image byl úspěšně nahrán!');
    }

    function deleteFrameImg(Event $event) {
        $this->authorize('update', $event);

        $user_id = $event->user_id;
        $pathPattern = "user_{$user_id}/event_{$event->id}/overlays/frame_img.*";

        // Najdi existující frame image
        $files = Storage::disk('private')->files("user_{$user_id}/event_{$event->id}/overlays");
        $frameFile = collect($files)->first(function ($file) use ($pathPattern) {
            return fnmatch($pathPattern, $file);
        });

        if ($frameFile) {
            Storage::disk('private')->delete($frameFile);
        }

        // Aktualizuj overlay záznam
        $event->overlays()->update(
            ['frame_img' => false]  // Označíme, že nemáme frame img
        );

        return response()->json([
            'success' => true,
            'message' => 'Overlay image byl úspěšně odstraněn!'
        ]);
    }

    function exportData(Event $event, $type) {
        $this->authorize('view', $event);

        if ($type === 'emails') {
            $guests = $event->guests()
                ->with('package')
                ->get();
            if ($guests->isEmpty()) {
                return back()->with('error', 'Neexistujú žiadne záznamy o hosťoch!');
            }

            $csvData = "Email, Balík, Cena, Počet_fotiek\n";
            foreach ($guests as $guest) {
                $packageNames = $guest->package->pluck('name')->join(';');
                $packagePrices = $guest->package->pluck('price')->join(';');
                $photoCount = $guest->photos_uploaded;
                $csvData .= "\"{$guest->email}\",\"{$packageNames}\",\"{$packagePrices}\",\"{$photoCount}\"\n";
            }

            $fileName = "event_{$event->id}_emails.csv";
            return response($csvData)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', "attachment; filename=\"{$fileName}\"");

        } else if ($type === 'photos') {
            $photos = $event->photos()->get();

            if ($photos->isEmpty()) {
                return back()->with('error', 'Neexistujú židne záznamy o fotkách!');
            }

            $zipFileName = "user_{$event->user_id}/event_{$event->id}/photos.zip";
            $zipFilePath = storage_path("app/private/{$zipFileName}");
            $zip = new \ZipArchive();
            
            if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
                foreach ($photos as $photo) {
                    $photoPath = storage_path("app/private/{$photo->path}");
                    if (file_exists($photoPath)) {
                        $zip->addFile($photoPath, basename($photoPath));
                    }
                }
                $zip->close();

                return response()->download($zipFilePath)->deleteFileAfterSend(true);
            } else {
                return back()->with('error', 'Nepodarilo sa vytvoriť ZIP súbor!');
            }
        }
    }
}