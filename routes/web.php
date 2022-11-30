<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SubscriptionController;

//Route::get('/', [ProductController::class, 'index']);
Route::post('/checkout', [ProductController::class, 'checkout'])->name('checkout');
Route::get('/success', [ProductController::class, 'success'])->name('checkout.success');
Route::get('/cancel', [ProductController::class, 'cancel'])->name('checkout.cancel');
Route::post('/webhook', [ProductController::class, 'webhook'])->name('checkout.webhook');
Route::get('/create-product', [SubscriptionController::class, 'createProduct'])->name('product.create');

//subscription
Route::get('/', [SubscriptionController::class, 'index']);
Route::post('/checkout-subscription', [SubscriptionController::class, 'checkout'])->name('checkout.subscription');
Route::get('/subscription/success', [SubscriptionController::class, 'success'])->name('checkout.subscription.success');
Route::get('/cancel', [SubscriptionController::class, 'cancel'])->name('checkout.subscription.cancel');
