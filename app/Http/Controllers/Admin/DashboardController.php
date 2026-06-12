<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Order;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('admin/dashboard', [
            'stats' => [
                'books' => Book::count(),
                'orders' => Order::count(),
                'revenue' => (float) Order::where('status', 'paid')->sum('total'),
            ],
        ]);
    }
}
