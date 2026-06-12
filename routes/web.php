<?php

use App\Http\Controllers\Admin\BookController as AdminBookController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\OrderHistoryController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [CatalogController::class, 'index'])->name('home');
Route::get('books/{book:slug}', [CatalogController::class, 'show'])->name('books.show');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::get('checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::post('checkout/{book}', [CheckoutController::class, 'store'])->name('checkout.store');
    // GET variant lets a guest's interrupted purchase resume via the
    // intended-URL redirect after login.
    Route::get('checkout/{book}', [CheckoutController::class, 'store'])->name('checkout.resume');

    Route::get('library', [LibraryController::class, 'index'])->name('library');
    Route::get('library/{book}/download', [LibraryController::class, 'download'])->name('library.download');

    Route::get('orders', [OrderHistoryController::class, 'index'])->name('orders.index');
});

Route::middleware(['auth', 'can:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');
    Route::resource('books', AdminBookController::class)->except(['show']);
    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
