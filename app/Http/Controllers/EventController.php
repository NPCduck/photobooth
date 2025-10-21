<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Event;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    use AuthorizesRequests;

    public function index() {
        $events = auth()->user()->events()->with(['details', 'packages', 'overlays', 'orders'])->get();

        return Inertia::render('Events/Index', [
            'events' => $events,
        ]);
    }

    public function show(Event $event) {
        $this->authorize('view', $event);
        $event->load(['details', 'packages', 'overlays', 'orders', 'actions', 'client']);

        return Inertia::render('Events/Show', [
            'event' => $event
        ]);
    }

    public function create() {
        return Inertia::render('Events/Create');
    }

    public function store(Request $request) {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'details.type' => 'required|string|max:255',
            'details.hosts' => 'required|integer',
            'details.status' => 'required|string|max:255',
            'details.date' => 'required|date',
            'details.time_start' => 'required|date_format:H:i',
            'details.time_end' => 'nullable|date_format:H:i',
            'details.status' => 'required|string|max:255',
            'details.loc_venue' => 'required|string|max:255',
            'details.loc_address' => 'required|string|max:255',
            'packages.*.name' => 'required|string|max:255',
            'packages.*.price' => 'required|numeric|min:0',
            'packages.*.photo_limit_total' => 'required|integer|min:0',
            'packages.*.photo_limit_person' => 'nullable|integer|min:0',
            'client.name' => 'required|string',
            'client.email' => 'required|string',
            'client.phone' => 'required|string|min:10|max:12',
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

                $path = $file->storeAs(
                    'user_' . $user_id . '/event_' . $event->id . '/overlays',
                    'landing_img.'.$file->getClientOriginalExtension(),
                    'private');
                $landing = true;
            }

            if (!empty($data['overlays']['frame_img'])) {
                $file = $data['overlays']['frame_img'];

                $path = $file->storeAs(
                    'user_' . $user_id . '/event_' . $event->id . '/overlays',
                    'frame_img.'.$file->getClientOriginalExtension(),
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

        $event->load(['details', 'packages', 'overlays']);

        return Inertia::render('Events/Edit', [
            'event' => $event,
        ]);
    }

    public function update(Request $request, Event $event) {
        $this->authorize('update', $event);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'details.type' => 'required|string|max:255',
            'details.hosts' => 'required|integer',
            'details.status' => 'required|string|max:255',
            'details.date' => 'required|date',
            'details.time_start' => 'required|date_format:H:i',
            'details.time_end' => 'nullable|date_format:H:i',
            'details.status' => 'required|string|max:255',
            'details.loc_venue' => 'required|string|max:255',
            'details.loc_address' => 'required|string|max:255',
            'packages.*.name' => 'required|string|max:255',
            'packages.*.price' => 'required|numeric|min:0',
            'packages.*.photo_limit_total' => 'required|integer|min:0',
            'packages.*.photo_limit_person' => 'nullable|integer|min:0',
            'client.name' => 'required|string',
            'client.email' => 'required|string',
            'client.phone' => 'required|string|min:10|max:12',
            'overlays.landing_img' => 'nullable|file|mimetypes:image/jpeg,image/png,image/webp|max:4096',
            'overlays.frame_img' => 'nullable|file|mimetypes:image/jpeg,image/png,image/webp|max:4096',
        ]);

        $event->update([
            'name' => $data['name'],
        ]);

        $event->client()->create($data['client']);

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

            if (!empty($data['overlays']['landing_img'])) {
                $file = $data['overlays']['landing_img'];

                $path = $file->storeAs(
                    'user_' . $user_id . '/event_' . $event->id . '/overlays',
                    'landing_img.'.$file->getClientOriginalExtension(),
                    'private');
                $landing = true;
            }

            if (!empty($data['overlays']['frame_img'])) {
                $file = $data['overlays']['frame_img'];

                $path = $file->storeAs(
                    'user_' . $user_id . '/event_' . $event->id . '/overlays',
                    'frame_img.'.$file->getClientOriginalExtension(),
                    'private');
                $frame = true;
            }

            $event->overlays()->create([
                'landing_img' => $landing,
                'frame_img' => $frame,
            ]);
        }

        $event->overlays()->updateOrCreate([], $data['overlays']);

        return redirect()->route('events.show', $event);
    }

    public function destroy(Event $event) {
        \Log::info('Destroy called for event ID: ' . $event->id);
        $this->authorize('delete', $event);
        $rmf_status = true;

        $user_id = auth()->id();
        $overlays_path = 'user_' . $user_id . '/event_' . $event->id . '/overlays';

        if (Storage::disk('private')->exists($overlays_path)) {
            if (!Storage::disk('private')->deleteDirectory($overlays_path)) {
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
}
