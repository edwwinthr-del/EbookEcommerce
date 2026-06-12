<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderHistoryController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('orders/index', [
            'orders' => $request->user()
                ->orders()
                ->with('items.book:id,title,slug')
                ->latest()
                ->paginate(10)
                ->through(fn (Order $order) => [
                    'id' => $order->id,
                    'total' => $order->total,
                    'status' => $order->status,
                    'created_at' => $order->created_at->toDateTimeString(),
                    'items' => $order->items->map(fn ($item) => [
                        'title' => $item->book?->title ?? '(no longer available)',
                        'slug' => $item->book?->slug,
                        'price' => $item->price,
                    ]),
                ])
                ->withQueryString(),
        ]);
    }
}
