<?php

namespace App\Listeners;

use App\Models\Order;
use Laravel\Cashier\Events\WebhookReceived;

class HandleStripeWebhook
{
    /**
     * The ONLY place where library entitlement is granted. Cashier has
     * already verified the Stripe signature before this event fires.
     */
    public function handle(WebhookReceived $event): void
    {
        if ($event->payload['type'] !== 'checkout.session.completed') {
            return;
        }

        $sessionId = $event->payload['data']['object']['id'] ?? null;

        if (! $sessionId) {
            return;
        }

        $order = Order::with('items')->where('stripe_session_id', $sessionId)->first();

        if (! $order || $order->status === 'paid') {
            return;
        }

        $order->update(['status' => 'paid']);

        foreach ($order->items as $item) {
            $order->user->books()->syncWithoutDetaching([
                $item->book_id => ['order_id' => $order->id],
            ]);
        }
    }
}
