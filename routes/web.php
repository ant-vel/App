<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
| @author  Gustavo Ocanto <gustavoocanto@gmail.com>
|
*/

Auth::routes();

Route::get('register/confirm/{token}/{email}', [
	'as' => 'register.confirm',
	'uses' => 'Auth\RegisterController@confirm']
);

// home
Route::get('/', ['as' => 'home', 'uses' => 'HomeController@index']);
Route::get('dashboard', ['as' => 'home', 'uses' => 'HomeController@dashboard']); //while refactoring

Route::group(['prefix' => 'home'], function () {
    Route::get('/', 'HomeController@index');
});

//users routes
require __DIR__ . '/web/users.php';

//business routes
require __DIR__ . '/web/business.php';

//Wpanel Routes
require __DIR__ . '/web/wpanel.php';

//products lists
require __DIR__ . '/web/products.php';

//wish lists
require __DIR__ . '/web/wish_lists.php';

//orders lists
require __DIR__ . '/web/orders.php';

//about
require __DIR__ . '/web/about.php';

//utilities
require __DIR__ . '/web/utilities.php';

