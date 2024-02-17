<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripePaymentController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/', [StripePaymentController::class, 'product']);
Route::get('/refund', [StripePaymentController::class, 'refund']);
Route::get('/refund_cancel', [StripePaymentController::class, 'refund_cancel']);
Route::get('/refund_view', [StripePaymentController::class, 'refund_view']);
Route::get('refundBack', [StripePaymentController::class, 'refundBack']);
Route::get('stripe', [StripePaymentController::class, 'stripe']);
Route::post('stripe', [StripePaymentController::class, 'stripePost'])->name('stripe.post');
