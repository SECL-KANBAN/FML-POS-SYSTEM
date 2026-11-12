<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\TransactionController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard', [
        'products' => Product::query()->orderBy('name')->get(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/cart/add/{product}', [CartController::class, 'add'])
        ->name('cart.add');

    Route::post('/cart/scan', [CartController::class, 'scan'])
        ->name('cart.scan');

    Route::post('/cart/decrease/{product}', [CartController::class, 'decrease'])
        ->name('cart.decrease');

    Route::post('/cart/remove/{product}', [CartController::class, 'remove'])
        ->name('cart.remove');

    Route::post('/checkout', [CheckoutController::class, 'process'])
        ->name('checkout.process');

    Route::get('/checkout/receipt/{receipt}', [CheckoutController::class, 'receipt'])
        ->name('checkout.receipt');

    Route::get('/checkout/receipt/{receipt}/pdf', [CheckoutController::class, 'pdf'])
        ->name('checkout.receipt.pdf');
});

require __DIR__ . '/auth.php';
