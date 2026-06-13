<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Order;
use App\Services\StripeCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class CheckoutController extends Controller
{
    /**
     * Start a purchase. The order and its item are priced from the
     * database; nothing from the client is trusted.
     */
    public function store(Request $request, Book $book, StripeCheckoutService $stripe): Response
    {
        abort_unless($book->status === 'published', 404);

        $user = $request->user();

        abort_if($user->ownsBook($book), 403, 'You already own this book.');

        // Free books skip Stripe entirely: the (DB) price is zero, so the
        // order is complete and the entitlement can be granted right away.
        if ((float) $book->price === 0.0) {
            $order = DB::transaction(function () use ($user, $book) {
                $order = Order::create([
                    'user_id' => $user->id,
                    'total' => 0,
                    'status' => 'paid',
                ]);

                $order->items()->create([
                    'book_id' => $book->id,
                    'price' => 0,
                ]);

                $user->books()->syncWithoutDetaching([
                    $book->id => ['order_id' => $order->id],
                ]);

                return $order;
            });

            return redirect()->route('checkout.success', ['order_id' => $order->id]);
        }

        $order = DB::transaction(function () use ($user, $book) {
            $order = Order::create([
                'user_id' => $user->id,
                'total' => $book->price,
                'status' => 'pending',
            ]);

            $order->items()->create([
                'book_id' => $book->id,
                'price' => $book->price,
            ]);

            return $order;
        });

        $session = $stripe->createSession($user, $book, $order);

        $order->update(['stripe_session_id' => $session->id]);

        return Inertia::location($session->url);
    }

    /**
     * Thank-you page only. Entitlement is granted exclusively by the
     * verified Stripe webhook, never here.
     */
    public function success(Request $request): \Inertia\Response
    {
        $order = null;

        if ($sessionId = $request->query('session_id')) {
            $order = Order::with('items.book:id,title,slug')
                ->where('stripe_session_id', $sessionId)
                ->where('user_id', $request->user()->id)
                ->first();
        } elseif ($orderId = $request->query('order_id')) {
            // Free-book orders have no Stripe session; look up by id,
            // scoped to the current user. Display only.
            $order = Order::with('items.book:id,title,slug')
                ->whereKey($orderId)
                ->where('user_id', $request->user()->id)
                ->first();
        }

        return Inertia::render('checkout/success', [
            'order' => $order ? [
                'id' => $order->id,
                'status' => $order->status,
                'total' => $order->total,
                'books' => $order->items->map(fn ($item) => [
                    'title' => $item->book?->title,
                    'slug' => $item->book?->slug,
                ]),
            ] : null,
        ]);
    }
}
