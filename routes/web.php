<?php

use App\Http\Controllers\PayPalController;
use App\Http\Controllers\SslCommerzPaymentController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\SubscriptionTypeController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    // return view('welcome');
    return phpinfo();
});
Route::get('news', function () {
    return view('emails.newsletterMail');
});

// Route::middleware('jwt:admin')->get('/test',function(){
//     return "something";
// });

// Route::controller(PayPalController::class)
//     ->prefix('paypal')
//     ->group(function () {
//         Route::get('payment', 'index')->name('create.payment');
//         Route::get('handle-payment/{id}', 'handlePayment')->name('make.payment');
//         Route::get('cancel-payment', 'paymentCancel')->name('cancel.payment');
//         Route::get('payment-success', 'paymentSuccess')->name('success.payment');
//     });
Route::controller(StripePaymentController::class)->group(function () {
    Route::get('pay', 'paynow');
    Route::get('stripe/{id}', 'stripe')->name('stripe');
    Route::post('stripe/{id}', 'stripePost')->name('stripe.post');
});



//SSLCOMMERZ Start

Route::get('/pay_view', [SslCommerzPaymentController::class, 'paynow']);

Route::post('/pay/{id}', [SslCommerzPaymentController::class, 'index']);
Route::get('/pay-via-ajax', [SslCommerzPaymentController::class, 'payViaAjax']);

Route::post('/success', [SslCommerzPaymentController::class, 'success']);
Route::post('/fail', [SslCommerzPaymentController::class, 'fail']);
Route::post('/cancel', [SslCommerzPaymentController::class, 'cancel']);

// Route::post('/ipn', [SslCommerzPaymentController::class, 'ipn']);
//SSLCOMMERZ END

//Sent Mail//
Route::get('/registration_mail', [SubscriptionTypeController::class, 'sentMail']);

// Route::get('/mail', function () {
//     return view('emails.subcriptionMail');
// });