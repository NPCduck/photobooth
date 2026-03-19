<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\QrController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\EventOrderController;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\EnsureUserIsSubscribed;

use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Landing', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('landing');

Route::get('/subscription', function () {
    return Inertia::render('Subscription/SubscriptionPlans', [
        'plans' => \App\Models\SubscriptionPlan::where('is_active', true)->get(),
    ]);
})->name('subscription.plans');


// Private image routes
Route::get('/private-svg/user_{user_id}/event_{event_id}/{path}/{file}', [ImageController::class, 'showSvgFrame'])
    ->where('path', '.*')
    ->name('private.frameSvg');

Route::get('/private-image/user_{user_id}/event_{event_id}/{path?}/{file}', [ImageController::class, 'showPrivateImage'])
        ->where('path', '.*')
        ->name('private.image');

Route::get('/private-image/user_{user_id}/event_{event_id}/{file}', [ImageController::class, 'showQrCode'])
    ->where('path', '.*')
    ->name('private.qrcode');


// Public photo capture routes - 🔴 SECURITY: s rate limiting!
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/capture/{token}', [PhotoController::class, 'show'])->name('capture.show');
    Route::post('/capture/{token}/check-email', [PhotoController::class, 'checkEmail'])->name('capture.checkEmail');
});

Route::post('/capture/{token}/upload', [PhotoController::class, 'upload'])
    ->middleware('throttle:20,1')  // 20 uploadov/minútu
    ->name('capture.upload');

Route::post('/capture/{token}/create-guest', [PhotoController::class, 'createGuest'])
    ->middleware('throttle:30,1')
    ->name('capture.createGuest');

Route::get('/capture/{token}/thank-you',
    fn ($token) => Inertia::render('Photo/ThankYou', ['token' => $token]))
    ->name('capture.thankYou');

// Orders accessible without authentication
Route::get('/order/{code}', [OrderController::class, 'showByCode'])
    ->name('orders.show.by-code');

Route::post('/orders/create-for-guest', [OrderController::class, 'storeForGuest'])
    ->middleware('throttle:10,1')
    ->name('orders.createForGuest');

// ==================== USER AUTHORIZED ROUTES ====================
Route::middleware('auth', 'subscribed')->group(function () {  
    Route::get('/dashboard', function () {
        $totalEvents = auth()->user()->events()->count();

        $totalUpcomingEvents = auth()->user()->events()
            ->whereHas('details', function ($query) {
                $query->where('status', 'aktuálny');
            })->count();

        $upcomingEventsList = auth()->user()->events()
            ->join('event_details', 'events.id', '=', 'event_details.event_id')
            ->where('event_details.status', 'aktuálny')
            ->orderBy('date')
            ->select('events.*')
            ->with('details')
            ->take(3)
            ->get();

        $totalOrders = auth()->user()->orders()->count();

        $totalRevenue = auth()->user()->orders()
            ->where('status', 'completed')
            ->sum('amount');

        $latestActions = auth()->user()->actions()
            ->with('event')
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'totalEvents' => $totalEvents,
            'totalUpcomingEvents' => $totalUpcomingEvents,
            'totalOrders' => $totalOrders,
            'totalRevenue' => $totalRevenue,
            'upcomingEventsList' => $upcomingEventsList,
            'latestActions' => $latestActions,
        ]);
    })->name('dashboard');

    // 🔴 SECURITY: QR Code management - s rate limiting
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/qr/activate/{event}', [QrController::class, 'activateQr'])->name('events.qr.activate');
        Route::post('/qr/deactivate/{event}', [QrController::class, 'deactivateQr'])->name('events.qr.deactivate');
    });

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Events
    Route::resource('events', EventController::class);
    Route::get('/events/{event}/photos', [EventController::class, 'photos'])
        ->name('events.photos');

    Route::get('events/{event}/orders', [EventController::class, 'ordersIndex'])
        ->name('events.orders.index');
    Route::post('/events/{event}/orders/bulk', [EventOrderController::class, 'bulkAction'])
        ->name('orders.bulkAction');
    Route::get('/events/{event}/orders/{order}', [EventOrderController::class, 'show'])
        ->name('events.orders.show');

    Route::post('events/{event}/frame-svg', [EventController::class, 'uploadFrameSvg'])
        ->name('events.frameSvg.upload');

    Route::get('events/{event}/export/{type}', [EventController::class, 'exportData'])
        ->name('events.export');

    Route::delete('events/{event}/frame-svg', [EventController::class, 'deleteFrameSvg'])
        ->name('events.frameSvg.delete');

    Route::post('events/{event}/landing-img', [EventController::class, 'uploadLandingImg'])
        ->name('events.landingImg.upload');
    Route::delete('events/{event}/landing-img', [EventController::class, 'deleteLandingImg'])
        ->name('events.landingImg.delete');

    Route::post('events/{event}/frame-img', [EventController::class, 'uploadFrameImg'])
        ->name('events.frameImg.upload');
    Route::delete('events/{event}/frame-img', [EventController::class, 'deleteFrameImg'])
        ->name('events.frameImg.delete');


    // Private photo access
    Route::get('private/image/{path}', [PhotoController::class, 'getPhotoUrl'])
        ->where('path', '.*')
        ->name('private.getPhotoUrl');

    // Orders
    Route::get('orders/', [OrderController::class, 'index'])->name('orders.index');
    Route::post('orders/create-for-guest', [OrderController::class, 'storeForGuest']);
    
    // 🔴 SECURITY: Hromadné operácie s rate limiting
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('orders/{order}/paid', [OrderController::class, 'markAsPaid']);
        Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);
    });
    
    Route::get('orders/{code}', [OrderController::class, 'showByCode'])->name('orders.show.bycode');
});
// ==================== - END - USER AUTHORIZED ROUTES ====================

// ==================== ADMIN AUTHORIZED ROUTES ====================
Route::middleware(['auth', AdminMiddleware::class])->group(function () {
    // Admin dashboard
    Route::get('/admin', function () {
        $events = \App\Models\Event::with('user')->get();
        $users = \App\Models\User::all();
        return Inertia::render('Admin/Dashboard', compact('events', 'users'));
    })->name('admin.dashboard');

    // User management
    Route::get('/admin/users', [ProfileController::class, 'index'])->name('admin.users.index');
});

require __DIR__.'/auth.php';
