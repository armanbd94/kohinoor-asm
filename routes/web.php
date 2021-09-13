<?php

use Illuminate\Support\Facades\Route;

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

Auth::routes(['register' => false]);

Route::group(['middleware' => ['auth']], function () {
    
    Route::get('/', 'HomeController@index')->name('dashboard');
    Route::get('dashboard-data/{start_date}/{end_date}', 'HomeController@dashboard_data');
    Route::get('unauthorized', 'HomeController@unauthorized')->name('unauthorized');
    Route::get('my-profile', 'MyProfileController@index')->name('my.profile');
    Route::post('update-profile', 'MyProfileController@updateProfile')->name('update.profile');
    Route::post('update-password', 'MyProfileController@updatePassword')->name('update.password');

    Route::get('coupon-received-report', 'ReceivedCouponController@index')->name('coupon.received.report');
    Route::post('coupon-received-report/datatable-data', 'ReceivedCouponController@get_datatable_data')->name('coupon.received.report.datatable.data');

    Route::get('inventory-report', 'InventoryController@index')->name('inventory.report');
    Route::post('inventory-report/datatable-data', 'InventoryController@get_datatable_data')->name('inventory.report.datatable.data');
});
