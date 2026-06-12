<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Order;
use App\Models\User;

class StripeCheckoutService
{
    /**
     * Create a Stripe Checkout session priced from the database.
     *
     * @return object{id: string, url: string}
     */
    public function createSession(User $user, Book $book, Order $order): object
    {
        $checkout = $user->checkout(
            [
                [
                    'price_data' => [
                        'currency' => config('cashier.currency'),
                        'product_data' => [
                            'name' => $book->title,
                        ],
                        // Price always comes from the database, never the client.
                        'unit_amount' => (int) round($book->price * 100),
                    ],
                    'quantity' => 1,
                ],
            ],
            [
                'success_url' => route('checkout.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('books.show', $book),
                'metadata' => [
                    'order_id' => $order->id,
                ],
            ],
        );

        return $checkout->asStripeCheckoutSession();
    }
}
