<?php

Route::resource('typepreferences', 'TypePreferencesController');

//points push notifications
Route::get('getPoints', 'UserController@getPoints');

//notices
Route::get('user/notices/check/{id?}', 'NoticesController@check');

Route::get('user/notices/list/{num?}', 'NoticesController@index');

Route::get('user/notices/{type?}', 'NoticesController@push');

Route::resource('user/notices', 'NoticesController');


Route::group(['prefix' => 'user', 'roles' => array_keys(trans('globals.roles')), 'middleware' => ['auth', 'roles']], function () {

    //Store Cart
    Route::get('user/orders/updateQuantity/{orderId}/{orderDetailId}/{newValue}', ['uses' => 'OrdersController@updateQuantity', 'as' => 'orders.update_order_quantity']);

    Route::get('product/save/{product}', ['uses' => 'OrdersController@saveForLater', 'as' => 'orders.save_for_later']);

    Route::get('orders/moveFrom/{origin}/to/{destination}/{productId}', ['uses' => 'OrdersController@moveFromOrder', 'as' => 'orders.move_from_order']);

    Route::get('orders/addToOrder/{orderId}/{productId}', ['uses' => 'OrdersController@addToOrderById', 'as' => 'orders.add_to_order_by_id']);

    Route::get('orders/checkOut/', ['uses' => 'OrdersController@checkOut', 'as' => 'orders.check_out']);

    Route::get('orders/checkOut/address/{addressId}', ['uses' => 'OrdersController@checkOutResume', 'as' => 'orders.check_out_address']);

    Route::get('orders/placeOrder/{type}', ['uses' => 'OrdersController@placeOrder', 'as' => 'orders.place_order']);

    Route::get('orders', ['uses' => 'OrdersController@usersOrders', 'as' => 'orders.show_orders']);

    //filtered by dates
    Route::post('orders', ['uses' => 'OrdersController@usersOrders', 'as' => 'orders.show_orders']);

    Route::get('orders/cancel/{orderId}', ['uses' => 'OrdersController@cancel', 'as' => 'orders.cancel']);

    Route::get('orders/showSeller/{orderId}', ['uses' => 'OrdersController@showSellerOrder', 'as' => 'orders.show_seller_order']);

    Route::get('orders/show/{orderId}', ['uses' => 'OrdersController@showOrder', 'as' => 'orders.show_order']);

    Route::get('orders/rate/{orderId}', ['uses' => 'OrdersController@rateOrder', 'as' => 'orders.rate_order']);

    //Route used to login an user and send it back to the product show

    Route::get('logAndShow/{productId}', ['uses' => 'ProductsController@show', 'as' => 'products.log_and_show']);

    Route::get('orders/close/{order_id}', ['uses' => 'OrdersController@closeOrder', 'as' => 'orders.close']);

    Route::get('modalSeeKeysPurchased', ['uses' => 'VirtualProductOrdersController@modalSeeKeysPurchased', 'as' => 'VirtualProductOrders.modalSeeKeysPurchased']);

    Route::get('showKeyVirtualProductPurchased/{idProduct}/{idOrder}', ['uses' => 'VirtualProductOrdersController@showKeyVirtualProductPurchased', 'as' => 'VirtualProductOrders.showKeyVirtualProductPurchased']);

    Route::get('orders/comment/{order_id}', ['uses' => 'OrdersController@commentOrder', 'as' => 'orders.comment']);

    Route::post('orders/storeComment', ['uses' => 'OrdersController@storeComment', 'as' => 'orders.store_comment']);

    Route::get('orders/delete/{order_id}/{type}', ['uses' => 'OrdersController@destroy', 'as' => 'orders.delete']);

    //Rates
    Route::post('rates/seller', ['uses' => 'OrdersController@rateSeller', 'as' => 'orders.rate_seller']);

    Route::post('rates/product', ['uses' => 'OrdersController@rateProduct', 'as' => 'orders.rate_product']);

    //Freeproducts
    Route::put('freeproducts/subscribe/{id}', ['uses' => 'FreeProductsController@subscribe', 'as' => 'freeproducts.subscribe']);

    Route::get('myFreeProducts', ['uses' => 'FreeProductsController@myFreeProducts', 'as' => 'freeproducts.my_free_products']);
});
