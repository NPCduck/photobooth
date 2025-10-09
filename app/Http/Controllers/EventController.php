<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Event;

class EventController extends Controller
{
    public function index() {
        $events = auth()->user()->events()->with(['details', 'packages', 'overlays', 'orders'])->get();

        return Inertia::render('Events/Index', [
            'events' => $events,
        ]);
    }

    public function show(Event $event) {
        $this->authorize('view', $event);
        $event->load(['details', 'packages', 'overlays', 'orders', 'actions']);

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
            'details.status' => 'required|string|max:255',
            'details.date' => 'required|date',
            'details.time_start' => 'required|string|max:255',
            'details.time_end' => 'nullable|string|max:255',
            'details.status' => 'required|string|max:255',
            'details.loc_venue' => 'required|string|max:255',
            'details.loc_address' => 'required|string|max:255',
            'packages.*.name' => 'required|string|max:255',
            'packages.*.price' => 'required|numeric|min:0',
            'packages.*.photo_limit' => 'nullable|integer',
            'overlays.landing_img' => 'nullable|string|max:255',
            'overlays.frame_img' => 'nullable|string|max:255',
        ]);

        $event = auth()->user()->events()->create([
            'name' => $data['name'],
        ]);

        $event->details()->create($data['details']);

        foreach ($data['packages'] as $package) {
            $event->packages()->create($package);
        }

        $event->overlays()->create($data['overlays']);

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
            'details.status' => 'required|string|max:255',
            'details.date' => 'required|date',
            'details.time_start' => 'required|string|max:255',
            'details.time_end' => 'nullable|string|max:255',
            'details.status' => 'required|string|max:255',
            'details.loc_venue' => 'required|string|max:255',
            'details.loc_address' => 'required|string|max:255',
            'packages.*.id' => 'sometimes|exists:event_packages,id',
            'packages.*.name' => 'required|string|max:255',
            'packages.*.price' => 'required|numeric|min:0',
            'packages.*.photo_limit' => 'nullable|integer',
            'overlays.landing_img' => 'nullable|string|max:255',
            'overlays.frame_img' => 'nullable|string|max:255',
        ]);

        $event->update([
            'name' => $data['name'],
        ]);

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

        $event->overlays()->updateOrCreate([], $data['overlays']);

        return redirect()->route('events.show', $event);
    }

    public function destroy(Event $event) {
        $this->authorize('delete', $event);
        $event->delete();

        return redirect()->route('events.index');
    }
}
