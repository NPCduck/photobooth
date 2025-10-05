<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Landing', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('landing');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', function () {
        $totalEvents = auth()->user()->events()->count();
        $totalUpcomingEvents = auth()->user()->events()->whereHas('details', function ($query) {
            $query->where('status', 'upcoming');
        })->count();
        $upcomingEventsList = auth()->user()->events()->whereHas('details', function ($query) {
            $query->where('status', 'upcoming');
        })->with('details')->get();

        $totalOrders = auth()->user()->orders()->count();
        $totalRevenue = auth()->user()->orders()->where('status', 'completed')->sum('amount');

        $latestActions = auth()->user()->actions()->with('event')->latest()->take(5)->get();

        return Inertia::render('Dashboard', [
            'totalEvents' => $totalEvents,
            'totalUpcomingEvents' => $totalUpcomingEvents,
            'totalOrders' => $totalOrders,
            'totalRevenue' => $totalRevenue,
            'upcomingEventsList' => $upcomingEventsList,
            'latestActions' => $latestActions,
        ]);
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('events', EventController::class);
});

require __DIR__.'/auth.php';
