<?php

use App\Http\Controllers\StripePaymenController;
use Illuminate\Support\Facades\Route;


Route::post('/stripe', [StripePaymenController::class, 'stripePost']);
