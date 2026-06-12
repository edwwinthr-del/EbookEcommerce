<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/orders/index', [
            'orders' => Order::with(['user:id,name,email', 'items.book:id,title'])
                ->latest()
                ->paginate(15)
                ->through(fn (Order $order) => [
                    'id' => $order->id,
                    'customer' => $order->user->name,
                    'email' => $order->user->email,
                    'items' => $order->items->map(fn ($item) => [
                        'title' => $item->book?->title ?? '(deleted book)',
                        'price' => $item->price,
                    ]),
                    'total' => $order->total,
                    'status' => $order->status,
                    'created_at' => $order->created_at->toDateTimeString(),
                ])
                ->withQueryString(),
        ]);
    }
}
