<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Order;
use App\Http\Controllers\Controller;
use App\Models\Photo;

use App\Jobs\GenerateInvoiceJob;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class EventOrderController extends Controller
{
    /**
     * Zoznam objednávok pre konkrétny event
     */
    public function index(Event $event) {
        $this->authorize('view', $event);

        $event->load([
            'orders.items',
            'orders.guest',
        ]);

        return inertia('Events/Orders/Index', [
            'event' => $event,
        ]);
    }

    /**
     * Zobrazenie detailov konktrétnej objednávky pre event
     */
    public function show(Event $event, Order $order) {
        $this->authorize('view', $event);

        if ($order->event_id !== $event->id) {
            abort(404);
        }

        $order->load(['guest', 'items', 'guest.photos']);

        return inertia('Events/Orders/Show', [
            'event' => $event,
            'order' => $order,
        ]);
    }

    /**
     * Hromadné akcie nad objednávkami eventu
     */
    public function bulkAction(Request $request, Event $event) {
        $this->authorize('update', $event);

        $data = $request->validate([
            'action'     => ['required', 'in:mark_paid,generate_invoice'],
            'order_ids'  => ['required', 'array'],
            'order_ids.*'=> ['integer'],
        ]);

        $orders = Order::where('event_id', $event->id)
            ->whereIn('id', $data['order_ids'])
            ->get();

        DB::transaction(function () use ($data, $orders) {

            foreach ($orders as $order) {
                match ($data['action']) {
                    'mark_paid' => $this->markOrderPaid($order),
                    'generate_invoice' => $this->generateInvoice($order),
                };
            }

        });

        return back()->with('success', 'Hromadná akcia bola úspešne vykonaná');
    }

    /**
     * Označenie objednávky ako zaplatenej
     */
    protected function markOrderPaid(Order $order): void {
        if ($order->status === 'paid') {
            return;
        }

        $order->update([
            'status' => 'paid',
            'payment_gateway' => 'admin',
            'payment_reference' => 'ADMIN-' . strtoupper(Str::random(8)),
        ]);
    }
}
