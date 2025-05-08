<?php
/**
 * @Desc:
 * @Author: 黄辉全
 * @Time: 2025/4/30 16:32
 */

use Illuminate\Support\Facades\Route;
use Plugin\PayPal\Controllers\PayPalController;

// Add the ->name() method to define the route name
Route::post('/paypal/create-order', [PayPalController::class, 'createOrder'])
    ->name('paypal.create-order');

Route::post('/paypal/capture-order', [PayPalController::class, 'captureOrder'])
    ->name('paypal.capture-order');
