<?php
/**
 * File name: api.php
 * Last modified: 2020.08.20 at 17:21:16
 * Author: Diginest
 * Copyright (c) 2021
 */

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('driver')->group(function () {
    Route::post('login', 'API\Driver\UserAPIController@login');
    Route::post('register', 'API\Driver\UserAPIController@register');
    Route::post('send_reset_link_email', 'API\UserAPIController@sendResetLinkEmail');
    Route::get('user', 'API\Driver\UserAPIController@user');
    Route::get('logout', 'API\Driver\UserAPIController@logout');
    Route::get('settings', 'API\Driver\UserAPIController@settings');
});

Route::prefix('manager')->group(function () {
    Route::post('login', 'API\Manager\UserAPIController@login');
    Route::post('register', 'API\Manager\UserAPIController@register');
    Route::post('send_reset_link_email', 'API\UserAPIController@sendResetLinkEmail');
    Route::get('user', 'API\Manager\UserAPIController@user');
    Route::get('logout', 'API\Manager\UserAPIController@logout');
    Route::get('settings', 'API\Manager\UserAPIController@settings');
});


Route::post('login', 'API\UserAPIController@login');
Route::post('register', 'API\UserAPIController@register');
Route::post('send_reset_link_email', 'API\UserAPIController@sendResetLinkEmail');
Route::get('user', 'API\UserAPIController@user');
Route::get('logout', 'API\UserAPIController@logout');
Route::get('settings', 'API\UserAPIController@settings');

Route::resource('cuisines', 'API\CuisineAPIController');
Route::resource('categories', 'API\CategoryAPIController');
Route::resource('restaurants', 'API\RestaurantAPIController');

//Restaurant Categories
Route::get('restaurantdetails/{rid}', 'API\RestaurantAPIController@getdetails');
Route::get('restaurant_categories/{rid}', 'API\RestaurantAPIController@getcategories');
Route::get('restaurant_foods/{rid}', 'API\RestaurantAPIController@getfoods');
Route::get('restaurant_featured/{rid}', 'API\RestaurantAPIController@getfeatured');
Route::get('restaurant_specials/{rid}', 'API\RestaurantAPIController@get_todays_special');
Route::get('restaurant_offers/{rid}', 'API\RestaurantAPIController@get_offers');

Route::get('getrestaurants/{mid}', 'API\MealsAPIController@getrestaurants');
Route::get('getmeals_foods/{mid}/{rid}', 'API\MealsAPIController@getfoods');

Route::get('restaurants_categories/{cid}', 'API\CategoryAPIController@get_restaurants');

Route::resource('faq_categories', 'API\FaqCategoryAPIController');
Route::get('foods/categories', 'API\FoodAPIController@categories');
Route::resource('foods', 'API\FoodAPIController');
Route::get('getfoods/{id}/{restaurantid}', 'API\FoodAPIController@getfoods');
Route::resource('galleries', 'API\GalleryAPIController');
Route::resource('food_reviews', 'API\FoodReviewAPIController');
Route::resource('nutrition', 'API\NutritionAPIController');
Route::resource('extras', 'API\ExtraAPIController');
Route::resource('extra_groups', 'API\ExtraGroupAPIController');
Route::resource('faqs', 'API\FaqAPIController');
Route::resource('restaurant_reviews', 'API\RestaurantReviewAPIController');
Route::get('get_restaurant_reviews/{rid}', 'API\RestaurantReviewAPIController@getrestaurants');
Route::resource('currencies', 'API\CurrencyAPIController');
Route::resource('slides', 'API\SlideAPIController')->except([
    'show'
]);

// Home Sections

Route::get('home', 'API\HomeAPIController@homesections');

// Cart 

Route::get('carts', 'API\NewCartAPIController@index');
Route::post('carts', 'API\NewCartAPIController@create');
Route::put('carts', 'API\NewCartAPIController@edit');
Route::delete('carts/{id}', 'API\NewCartAPIController@delete');
Route::post('clear_cart', 'API\NewCartAPIController@clearcart');

// Order

Route::get('myorders', 'API\NewOrderAPIController@myOrders');
Route::get('orders/{id}', 'API\NewOrderAPIController@show');
Route::post('create_order', 'API\NewOrderAPIController@store');
Route::put('cancel_order/{oid}', 'API\NewOrderAPIController@cancel_order');

Route::middleware('auth:api')->group(function () {
    Route::group(['middleware' => ['role:driver']], function () {
        Route::prefix('driver')->group(function () {
            Route::resource('orders', 'API\OrderAPIController');
            Route::resource('notifications', 'API\NotificationAPIController');
            Route::post('users/{id}', 'API\UserAPIController@update');
            Route::resource('faq_categories', 'API\FaqCategoryAPIController');
            Route::resource('faqs', 'API\FaqAPIController');
        });
    });
    Route::group(['middleware' => ['role:manager']], function () {
        Route::prefix('manager')->group(function () {
            Route::post('users/{id}', 'API\UserAPIController@update');
            Route::get('users/drivers_of_restaurant/{id}', 'API\Manager\UserAPIController@driversOfRestaurant');
            Route::get('dashboard/{id}', 'API\DashboardAPIController@manager');
            Route::resource('restaurants', 'API\Manager\RestaurantAPIController');
            Route::resource('faq_categories', 'API\FaqCategoryAPIController');
            Route::resource('faqs', 'API\FaqAPIController');
        });
    });
    Route::post('users/{id}', 'API\UserAPIController@update');

    Route::resource('order_statuses', 'API\OrderStatusAPIController');

    Route::get('payments/byMonth', 'API\PaymentAPIController@byMonth')->name('payments.byMonth');
    Route::resource('payments', 'API\PaymentAPIController');

    Route::get('favorites/exist', 'API\FavoriteAPIController@exist');
    Route::resource('favorites', 'API\FavoriteAPIController');

    // Route::resource('orders', 'API\OrderAPIController');

    Route::resource('food_orders', 'API\FoodOrderAPIController');

    Route::resource('notifications', 'API\NotificationAPIController');

    Route::get('carts/count', 'API\CartAPIController@count')->name('carts.count');
    // Route::resource('carts', 'API\CartAPIController');



    Route::resource('delivery_addresses', 'API\DeliveryAddressAPIController');
    Route::post('create_address', 'API\DeliveryAddressAPIController@create');
    Route::put('edit_address', 'API\DeliveryAddressAPIController@edit');

    Route::resource('area', 'API\AreaAPIController');

    Route::resource('delivery_charges', 'API\DeliveryChargeAPIController');

    Route::get('get_delivery_charges/{id}/{restaurantid}', 'API\DeliveryChargeAPIController@search');
    Route::get('get_area_restaurants/{restaurantid}', 'API\DeliveryChargeAPIController@getarea');

    Route::resource('drivers', 'API\DriverAPIController');

    Route::resource('earnings', 'API\EarningAPIController');

    Route::resource('driversPayouts', 'API\DriversPayoutAPIController');

    Route::resource('restaurantsPayouts', 'API\RestaurantsPayoutAPIController');

    Route::resource('coupons', 'API\CouponAPIController')->except([
        'show'
    ]);

    Route::post('applycoupon', 'API\CouponAPIController@apply_coupon');
});