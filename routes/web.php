<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'PagesController@root')->name('root');

Auth::routes();

Route::group(['middleware' => 'auth'],function (){
    Route::get('/email_verify_notice', 'PagesController@emailVerifyNotice')->name('email_verify_notice');
    Route::get('/email_verification/verify', 'EmailVerificationController@verify')->name('email_verification.verify');
    Route::get('/email_verification/send', 'EmailVerificationController@send')->name('email_verification.send');

    Route::group(['middleware' => 'email_verified'], function() {
        // 收藏商品
        Route::post('products/{product}/favorite','ProductsController@favorite')->name('products.favorite');
        Route::delete('products/{product}/favorite','ProductsController@disFavorite')->name('products.disFavorite');
        // 收货地址
        Route::get('user_addresses', 'UserAddressesController@index')->name('user_addresses.index');
        Route::get('user_addresses/create','UserAddressesController@create')->name('user_addresses.create');
        Route::post('user_addresses','UserAddressesController@store')->name('user_addresses.store');
        Route::get('user_addresses/{user_address}','UserAddressesController@edit')->name('user_addresses.edit');
        Route::put('user_addresses/{user_address}','UserAddressesController@update')->name('user_addresses.update');
        Route::delete('user_addresses/{user_address}','UserAddressesController@destroy')->name('user_addresses.destroy');

        // 购物车
        Route::get('cart','CartController@index')->name('cart.index');
        Route::post('cart', 'CartController@add')->name('cart.add');
        Route::delete('cart/{sku}','CartController@remove')->name('cart.remove');
        // 订单
        Route::post('order','OrderController@store')->name('order.store');
        Route::get('order','OrderController@index')->name('order.index');
        Route::get('order/{order}','OrderController@show')->name('order.show');
        // 支付
        Route::get('payment/{order}/alipay', 'PaymentController@payByAlipay')->name('payment.alipay');
        Route::get('payment/alipay/return', 'PaymentController@alipayReturn')->name('payment.alipay.return');

        // 确认收货
        Route::post('order/{order}/received','OrderController@received')->name('order.received');
        Route::get('order/{order}/review', 'OrderController@review')->name('order.review.show');
        // 评价
        Route::post('order/{order}/review','OrderController@sendReview')->name('order.review.store');
        //
        Route::post('order/{order}/apply_refund','OrderController@applyRefund')->name('order.apply_refund');
        Route::get('coupon_code/{code}','CouponCodesController@show')->name('coupon_codes.show');
        // 众筹
        Route::post('crowdfunding_orders', 'OrderController@crowdfunding')->name('crowdfunding_orders.store');
        Route::post('seckill_orders', 'OrderController@seckill')->name('seckill_orders.store');
    });
});

Route::get('products','ProductsController@index')->name('products.index');
Route::get('products/{product}','ProductsController@show')->name('products.show')->where('product', '[0-9]+');

Route::post('payment/alipay/notify', 'PaymentController@alipayNotify')->name('payment.alipay.notify');

