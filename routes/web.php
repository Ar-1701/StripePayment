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
Route::get('refundBack', [StripePaymentController::class, '']);
Route::get('stripe', [StripePaymentController::class, 'stripe']);
Route::post('stripe', [StripePaymentController::class, 'stripePost'])->name('stripe.post');
