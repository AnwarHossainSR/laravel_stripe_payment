<?php

use App\Http\Controllers\StripePaymenController;
use App\Http\Controllers\SubscriptionApiController;
use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::post('/stripe', [StripePaymenController::class, 'stripePost']);

//subscription
Route::get('/', [SubscriptionApiController::class, 'index']);
Route::post('/checkout-subscription', [SubscriptionApiController::class, 'checkout'])->name('checkout.subscription');
Route::get('/subscription/success', [SubscriptionApiController::class, 'success'])->name('checkout.subscription.success.api');
Route::get('/subscription/failure', [SubscriptionApiController::class, 'failure'])->name('checkout.subscription.failure.api');

Route::get('/subscription/cancel', [SubscriptionApiController::class, 'subscriptionCancel'])->name('checkout.subscription.cancel.api');
Route::get('/create-product', [SubscriptionApiController::class, 'createProduct'])->name('product.create');
