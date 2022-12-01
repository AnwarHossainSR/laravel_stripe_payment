<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SubscriptionController;

//Route::get('/', [ProductController::class, 'index']);
// Route::post('/checkout', [ProductController::class, 'checkout'])->name('checkout');
// Route::get('/success', [ProductController::class, 'success'])->name('checkout.success');
// Route::get('/cancel', [ProductController::class, 'cancel'])->name('checkout.cancel');
// Route::post('/webhooks/stripe', [ProductController::class, 'webhook'])->name('checkout.webhook');
// Route::get('/create-product', [SubscriptionController::class, 'createProduct'])->name('product.create');

//subscription
Route::get('/', [SubscriptionController::class, 'index']);
Route::post('/checkout-subscription', [SubscriptionController::class, 'checkout'])->name('checkout.subscription');
Route::get('/subscription/success', [SubscriptionController::class, 'success'])->name('checkout.subscription.success');
Route::get('/subscription/failure', [SubscriptionController::class, 'failure'])->name('checkout.subscription.failure');
Route::get('/create-product', [SubscriptionController::class, 'createProduct'])->name('product.create');

Route::get('/subscription/cancel', [SubscriptionController::class, 'subscriptionCancel'])->name('checkout.subscription.cancel');
Route::post('/webhooks/stripe', [SubscriptionController::class, 'webhook'])->name('checkout.webhook');
