<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BoostingController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CredentialController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PayPalController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ComplainController;
use App\Http\Controllers\Contact;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\NewsletterSubscriberController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\SslCommerzPaymentController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\SubscriptedController;
use App\Http\Controllers\SubscriptionTypeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
 */


// Guest route start----------------------------------------------------------------
Route::get('category/all', [CategoryController::class, 'category_all']);
Route::get('subcategory/all', [SubCategoryController::class, 'subcategory_all']);
Route::get('brand/all', [BrandController::class, 'brand_all']);
Route::get('location/all', [LocationController::class, 'location_all']);
Route::get('area/all', [AreaController::class, 'area_all']);
Route::get('slider/all', [SliderController::class, 'slider_index']);

// FAQ ROUTE -----------------------
Route::get('faq/all', [FaqController::class, 'faq_index']);
//Contact Us
Route::post('contact/store', [ContactController::class, 'contact_store']);
// Newsletter Subscription
Route::post('newsletter/subscription', [SubscriptedController::class, 'newsletterSubscripted']);
// Product section
Route::controller(ProductController::class)->group(function () {
    Route::get('/products', 'products');
    Route::get('/products-details/{id}', 'show');
    Route::get('/products/search', 'product_search');
    // Route::get('/filter-products', 'product_filter');
    Route::get('filter/products/{type}', 'productFilter');
});

Route::controller(AuthController::class)->group(function () {
    Route::post('user/register', 'register');
    Route::post('user/login', 'login');
    Route::post('user/forget-password', 'ForgetPassword');
    Route::put('password-update', 'updatePassword');
    Route::put('email-verified', 'EmailVerified');
});
//Setting Route................................................................
Route::controller(SettingController::class)->prefix('setting')->group(function () {
    Route::get('/index', 'setting_index');
});
// Guest route End----------------------------------------------------------------

// User Route with Prefix ---------------------------------------------------------
Route::group([
    'middleware' => 'jwt:api', ['auth', 'verified'],
    'prefix' => 'auth',
], function () {
    Route::controller(AuthController::class)->group(function () {
        Route::get('/profile', 'profile');
        Route::get('/logged_in_user', 'me');
        Route::get('refresh', 'Refresh');
        Route::put('update_profile', 'Update');
        Route::delete('delete', 'Delete');
        Route::post('logout', 'userlogout');
    });
});
// User Routes without Prefix --------------------------------
Route::group([
    'middleware' => 'jwt:api', ['auth', 'verified']
], function () {
    // Products
    Route::controller(ProductController::class)->prefix('product')->group(function () {
        Route::post('/store', 'store');
        Route::get('/edit/{id}', 'edit');
        Route::put('/update/{id}', 'update');
        Route::put('/sold/{id}', 'sold');
        Route::get('sold_out/all', 'userSoldout');
        Route::get('/userProduct', 'userProduct');
        Route::delete('/delete/{id}', 'destroy');
    });
    // Product Boosting -------------------------------------
    Route::controller(BoostingController::class)->prefix('post')->group(function () {
        Route::post('boosting/{id}', 'boosting_product');
        Route::post('boostinginfostore/{id}', 'boostingInfoStore');
        Route::post('stripe/payment/{id}', 'StripePayment');
    });
    Route::get('userpanel', [DashboardController::class, 'userPanel']);
    //comments Route
    // Route::post('/comments/index', [ComplainController::class, 'index']);
    Route::post('/comments/store', [ComplainController::class, 'comment_store']);
    // Route::get('/comments/show/{id}', [ComplainController::class, 'show']);

    //Subscription
    // Route::group(['prefix' => 'subscription'], function () {
    //     Route::post('/store', [SubscriptionController::class, 'store'])->name('subscription.store');
    // });
    Route::get('/paypal/credentials', [PayPalController::class, 'paypalCredential']);
    Route::post('/paypal/info_store', [PayPalController::class, 'PaypalInfoStore']);
    Route::get('/membership', [SubscriptionTypeController::class, 'membership']);

    Route::get('/subcription_mail', [SubscriptionTypeController::class, 'sentMail'])->name('mailsent');
    //SSLCOMMERZ Start

    Route::get('/pay_view', [SslCommerzPaymentController::class, 'paynow']);

    Route::get('/pay/{id}', [SslCommerzPaymentController::class, 'index']);
    Route::post('/pay-via-ajax', [SslCommerzPaymentController::class, 'payViaAjax']);

    Route::post('/success', [SslCommerzPaymentController::class, 'success']);
    Route::post('/fail', [SslCommerzPaymentController::class, 'fail']);
    Route::post('/cancel', [SslCommerzPaymentController::class, 'cancel']);
    // Stripe Start
    Route::post('stripe/checkout', [StripePaymentController::class, 'Payment']);
    Route::controller(StripePaymentController::class)->group(function () {
        Route::post('stripe/{id}', 'stripePost')->name('stripe.post');
        Route::post('stripe_payment_info_store', 'StripeInfoStore');
    });

    Route::get('stripe_publish_key', [CredentialController::class, 'stripePublishKey']);
});

// Route::get('stripe/',[StripePaymentController::class, 'StripePayment']);
// Admin Route................................................................
Route::post('admin/login', [AdminController::class, 'Login']);
Route::group([
    'middleware' => 'jwt:admin',
    'prefix' => 'admin',
], function () {
    Route::controller(AdminController::class)->group(function () {

        Route::get('/logged_in_user', 'me');
        Route::post('logout', 'Adminlogout');
        Route::post('register', 'Register');

        Route::get('profile', 'profile');

        Route::get('all/admin/users', 'AdminUser');
        Route::get('all/customer_user', 'CustomerUser');
        Route::get('all/banned_unbanned/customer_user/', 'BannedUnbannedCustomerUser');

        Route::get('customer_user/search', 'CustomerUserSearch');

        Route::put('customer_user/banned/{id}', 'CustomerUserBanned');
        Route::put('customer_user/unbanned/{id}', 'CustomerUserUnbanned');

        Route::get('edit', 'AdminEdit');
        Route::put('update', 'Update');

        Route::put('admin_user_update/{id}', 'AdminUserUpdate');
        Route::delete('admin_user_delete/{id}', 'AdminDelete');
        Route::get('admin_user_search', 'AdminUserSearch');

        Route::delete('user/delete/{id}', 'UserDelete');
    });
    Route::get('adminpanel', [DashboardController::class, 'adminPanel']);
    // Report of Products................................................................
    Route::controller(ReportController::class)->prefix('product')->group(function () {
        Route::get('/report', 'report');
        Route::get('/timePosts', 'timePosts');
        Route::get('/timeReport', 'timeReport');
    });

    // Products................................................................
    Route::controller(ProductController::class)->prefix('product')->group(function () {
        Route::get('/', 'index');
        Route::post('/active/{id}', 'Active');
        Route::post('/inActive/{id}', 'InActive');
        Route::delete('/delete/{id}', 'destroy');
    });

    //Category Route................................................................

    Route::controller(CategoryController::class)->prefix('category')->group(function () {
        Route::get('/index', 'category_index');
        Route::post('/store', 'category_store');

        Route::put('/update/{id}', 'category_update');
        Route::get('/search', 'category_search');
        Route::delete('/delete/{id}', 'category_delete');
    });

    // SubCategory Route ................................................
    Route::controller(SubCategoryController::class)->prefix('subcategory')->group(function () {
        Route::get('/index', 'subcategory_index');
        Route::post('/store', 'subcategory_store');

        Route::put('/update/{id}', 'subcategory_update');
        Route::get('/search', 'sub_category_search');
        Route::delete('/delete/{id}', 'subcategory_delete');
    });

    // Brand Route................................................................
    Route::controller(BrandController::class)->prefix('brand')->group(function () {
        Route::get('/index', 'brand_index');
        Route::post('/store', 'brand_store');

        Route::put('/update/{id}', 'brand_update');
        Route::get('/search', 'brand_search');
        Route::delete('/delete/{id}', 'brand_delete');
    });

    // Location Route................................................................
    Route::controller(LocationController::class)->prefix('location')->group(function () {

        Route::get('/index', 'location_index');
        Route::post('/store', 'location_store');

        Route::put('/update/{id}', 'location_update');
        Route::get('/search', 'location_search');
        Route::delete('/delete/{id}', 'location_delete');
    });

    //Area Route................................................................
    Route::controller(AreaController::class)->prefix('area')->group(function () {
        Route::get('/index', 'area_index');
        Route::post('/store', 'area_store');

        Route::put('/update/{id}', 'area_update');
        Route::get('/search', 'area_search');
        Route::delete('/delete/{id}', 'area_delete');
    });

    //Setting Route................................................................
    Route::controller(SettingController::class)->prefix('setting')->group(function () {

        Route::put('/update', 'setting_update');
    });

    // Slider Controller.......................................
    Route::controller(SliderController::class)->prefix('slider')->group(function () {

        Route::post('/store', 'slider_store');
        Route::put('/update/{id}', 'slider_update');
        Route::delete('/delete/{id}', 'slider_delete');
    });

    // Database Route................................................................
    Route::get('/database/backup', [BackupController::class, 'backupDatabase']);
    Route::get('/database/show', [BackupController::class, 'ShowDatabase']);
    Route::delete('/database/delete/', [BackupController::class, 'DeleteDatabase']);
    Route::get('/database/restore', [BackupController::class, 'RestoreDatabase']);

    // credential................................................................
    Route::controller(CredentialController::class)->group(function () {
        Route::post('/paypal/credential', 'paypal_credential');
        Route::post('/stripe/credential', 'stripe_credential');
        Route::post('/sslcommerz/credential', 'ssl_credential');
        Route::post('/mail/credential', 'mail_credential');
    });

    //Subscription................................................................
    Route::controller(SubscriptionTypeController::class)->prefix('subscription')->group(function () {
        Route::get('/', 'index')->name('subscriptionType.index');
        Route::post('/store', 'store')->name('subscriptionType.store');
        Route::get('/edit/{id}', 'edit')->name('subscriptionType.edit');
        Route::put('/update/{id}', 'update')->name('subscriptionType.update');
        Route::delete('/delete/{id}', 'destroy')->name('subscriptionType.delete');
    });
    // Faq Route......................................................
    Route::controller(FaqController::class)->prefix('faq')->group(function () {
        Route::post('/store', 'faq_store');
        Route::get('/edit/{id}', 'faq_edit');
        Route::put('/update/{id}', 'faq_update');
        Route::delete('/delete/{id}', 'faq_destroy');
    });

    // Contact Us...............................................................
    Route::controller(ContactController::class)->prefix('contact')->group(function () {
        Route::get('/', 'contact_index');
        Route::put('/update/{id}', 'contact_update');
    });

    //All Permissions................................................................
    Route::get('/permissions', [RoleController::class, 'permissions']);
    //Role................................................................
    Route::controller(RoleController::class)->prefix('role')->group(function () {
        Route::get('/search', 'role_search');
        Route::get('/', 'index');
        Route::get('/all', 'allRole');
        Route::get('/create', 'create');
        Route::post('/store', 'store');
        Route::get('/edit/{id}', 'edit');
        Route::put('/update/{id}', 'update');
        Route::delete('/delete/{id}', 'destroy');
    });
    // Newsletter Subscription Admin Panel................................................................
    Route::controller(NewsletterController::class)->prefix('newsletter')->group(function () {
        Route::get('all', 'index');
        Route::post('store', 'store');
        Route::get('search', 'search');
        Route::put('update/{id}', 'update');
        Route::delete('delete/{id}', 'delete');
        Route::post('publish/{id}', 'publish');
        // Route::delete('delete/{id}', 'DeleteSubscriptedUser');
    });
    Route::controller(NewsletterSubscriberController::class)->prefix('newsletter_subscriber')->group(function () {
        Route::get('all', 'index');
    });

    // Show Boosting User------------------------------------
    Route::controller(BoostingController::class)->prefix('boost')->group(function () {

        Route::get('/', 'view_boost', 'boosting_index');
    });
});
