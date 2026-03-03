<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Event;
use App\Models\EventGuest;
use App\Models\EventPackage;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function storeForGuest(Request $request) {
        $request->validate([
            'guest_id'   => ['required', 'exists:event_guests,id'],
            'package_id' => ['required', 'exists:event_packages,id'],
        ]);

        $guest   = EventGuest::with('event')->findOrFail($request->guest_id);
        $event   = $guest->event;
        
        // 🔴 SECURITY: Balík MUSÍ patriť k eventu hosta!
        $package = EventPackage::where('id', $request->package_id)
            ->where('event_id', $event->id)
            ->firstOrFail();

        return DB::transaction(function () use ($guest, $event, $package) {

            // 1️⃣ Order
            $order = Order::create([
                'user_id'          => $event->user_id,
                'event_id'         => $event->id,
                'guest_id'         => $guest->id,
                'code'             => Str::uuid(),
                'status'           => 'pending',
                'amount'           => $package->price,
                'is_test'          => true,
                'payment_gateway'  => null,
                'payment_reference'=> null,
            ]);

            // 2️⃣ Order item
            $order->items()->create([
                'package_id'  => $package->id,
                'name'        => $package->name,
                'unit_price'  => $package->price,
                'quantity'    => 1,
                'total_price' => $package->price,
            ]);

            return response()->json([
                'message' => 'Testovacia objednávka vytvorená',
                'order'   => $order->load('items'),
            ], 201);
        });
    }

    public function storeForGuestInternal(
        EventGuest $guest,
        Event $event,
        int $packageId
    ): Order {
        // 🔴 SECURITY: Balík MUSÍ patriť k eventu
        $package = EventPackage::where('id', $packageId)
            ->where('event_id', $event->id)
            ->firstOrFail();

        // 🔴 SECURITY: Snapshot ceny - nikdy sa nezmení
        $snapshotPrice = $package->price;

        $order = Order::create([
            'user_id' => $event->user_id,
            'event_id' => $event->id,
            'guest_id' => $guest->id,
            'code' => Str::uuid(),
            'status' => 'pending',
            'amount' => $snapshotPrice,
        ]);

        // 🔴 SECURITY: OrderItem je jediný zdroj pravdy
        $order->items()->create([
            'package_id' => $package->id,
            'name' => $package->name,
            'unit_price' => $snapshotPrice,
            'quantity' => 1,
            'total_price' => $snapshotPrice,
        ]);

        return $order;
    }

    /**
     * Detail objednávky podľa kódu (guest pohľad)
     */
    public function showByCode(string $code) {
        // 🔴 SECURITY: Iba autentifikovaní useri - iba vlastné objednávky
        $order = Order::with(['items', 'guest', 'event'])
            ->where('code', $code)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return response()->json($order);
    }

    /**
     * Zoznam objednávok pre prihláseného usera (admin / klient)
     */
    public function index(Request $request) {
        $orders = Order::with(['event', 'guest'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        return response()->json($orders);
    }

    /**
     * Simulácia platby (TEST)
     */
    public function markAsPaid(Order $order) {
        $this->authorize('update', $order);

        // 🔴 SECURITY: Iba pending objednávky môžu byť označené
        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Túto objednávku sa nedá označiť ako zaplatenú',
            ], 422);
        }

        $order->update([
            'status'            => 'paid',
            'payment_gateway'   => 'test',
            'payment_reference' => 'TEST-' . strtoupper(Str::random(8)),
        ]);

        return response()->json([
            'message' => 'Objednávka označená ako zaplatená',
            'order'   => $order,
        ]);
    }

    /**
     * Zrušenie objednávky
     */
    public function cancel(Order $order) {
        $this->authorize('update', $order); 

        if ($order->status === 'paid') {
            return response()->json([
                'message' => 'Zaplatenú objednávku nemožno zrušiť',
            ], 422);
        }

        $order->update([
            'status' => 'cancelled',
        ]);

        return response()->json([
            'message' => 'Objednávka zrušená',
        ]);
    }
}
