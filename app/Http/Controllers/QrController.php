<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;

class QrController extends Controller
{
    function activateQr(Event $event) {
        // 🔴 SECURITY: Explicitná autorizácia
        $this->authorize('update', $event);
        
        $event->qr_active = true;
        $event->save();

        return redirect()->back()->with('success', 'QR kód bol aktivovaný.');
    }

    function deactivateQr(Event $event) {
        // 🔴 SECURITY: Explicitná autorizácia
        $this->authorize('update', $event);
        
        $event->qr_active = false;
        $event->save();

        return redirect()->back()->with('success', 'QR kód bol deaktivovaný.');
    }
}
