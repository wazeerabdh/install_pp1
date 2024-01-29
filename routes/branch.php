<?php

use App\Http\Controllers\Branch\OrderController;
use App\Http\Controllers\Branch\POSController;
use App\Http\Controllers\Branch\SystemController;
use Illuminate\Support\Facades\Route;


Route::group(['namespace' => 'Branch', 'as' => 'branch.'], function () {
    /*authentication*/
    Route::group(['namespace' => 'Auth', 'prefix' => 'auth', 'as' => 'auth.'], function () {
        Route::get('/code/captcha/{tmp}', 'LoginController@captcha')->name('default-captcha');
        Route::get('login', 'LoginController@login')->name('login');
        Route::post('login', 'LoginController@submit');
        Route::get('logout', 'LoginController@logout')->name('logout');
    });
    /*authentication*/

    Route::group(['middleware' => ['branch']], function () {
        Route::get('/', [SystemController::class, 'dashboard'])->name('dashboard');
        Route::get('settings', [SystemController::class, 'settings'])->name('settings');
        Route::post('settings', [SystemController::class, 'settingsUpdate']);
        Route::post('settings-password', [SystemController::class, 'settingsPasswordUpdate'])->name('settings-password');
        Route::post('order-stats', [SystemController::class, 'orderStats'])->name('order-stats');
        Route::get('/get-restaurant-data', [SystemController::class, 'restaurantData'])->name('get-restaurant-data');
        Route::get('dashboard/earning-statistics', [SystemController::class, 'getEarningStatistics'])->name('dashboard.earning-statistics');
        Route::get('ignore-check-order', [SystemController::class, 'ignoreCheckOrder'])->name('ignore-check-order');

        Route::group(['prefix' => 'pos', 'as' => 'pos.'], function () {
            Route::get('/', [POSController::class, 'index'])->name('index');
            Route::get('quick-view', [POSController::class, 'quickView'])->name('quick-view');
            Route::post('variant_price', [POSController::class, 'variantPrice'])->name('variant_price');
            Route::post('add-to-cart', [POSController::class, 'addToCart'])->name('add-to-cart');
            Route::post('remove-from-cart', [POSController::class, 'removeFromCart'])->name('remove-from-cart');
            Route::post('cart-items', [POSController::class, 'cartItems'])->name('cart_items');
            Route::post('update-quantity', [POSController::class, 'updateQuantity'])->name('updateQuantity');
            Route::post('empty-cart', [POSController::class, 'emptyCart'])->name('emptyCart');
            Route::post('tax', [POSController::class, 'updateTax'])->name('tax');
            Route::post('discount', [POSController::class, 'updateDiscount'])->name('discount');
            Route::get('customers', [POSController::class, 'getCustomers'])->name('customers');
            Route::post('order', [POSController::class, 'placeOrder'])->name('order');
            Route::get('orders', [POSController::class, 'orderList'])->name('orders');
            Route::get('order-details/{id}', [POSController::class, 'orderDetails'])->name('order-details');
            Route::get('invoice/{id}', [POSController::class, 'generateInvoice']);
            Route::any('store-keys', [POSController::class, 'storeKeys'])->name('store-keys');
            Route::post('customer-store', [POSController::class, 'customerStore'])->name('customer-store');
            Route::get('orders/export', [POSController::class, 'exportOrders'])->name('orders.export');

        });

        Route::group(['prefix' => 'orders', 'as' => 'orders.'], function () {
            Route::get('list/{status}', [OrderController::class, 'list'])->name('list');
            Route::get('details/{id}', [OrderController::class, 'details'])->name('details');
            Route::get('status', [OrderController::class, 'status'])->name('status');
            Route::get('add-delivery-man/{order_id}/{delivery_man_id}', [OrderController::class, 'addDeliveryMan'])->name('add-delivery-man');
            Route::get('payment-status', [OrderController::class, 'paymentStatus'])->name('payment-status');
            Route::get('generate-invoice/{id}', [OrderController::class, 'generateInvoice'])->name('generate-invoice');
            Route::post('add-payment-ref-code/{id}', [OrderController::class, 'addPaymentRefCode'])->name('add-payment-ref-code');
            Route::get('export/{status}', [OrderController::class, 'exportOrders'])->name('export');
        });

        Route::group(['prefix' => 'order', 'as' => 'order.'], function () {
            Route::get('list/{status}', 'OrderController@list')->name('list');
            Route::put('status-update/{id}', 'OrderController@status')->name('status-update');
            Route::get('view/{id}', 'OrderController@view')->name('view');
            Route::post('update-shipping/{id}', 'OrderController@updateShipping')->name('update-shipping');
            Route::delete('delete/{id}', 'OrderController@delete')->name('delete');
            Route::post('search', 'OrderController@search')->name('search');
        });
    });
});
