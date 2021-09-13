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

Route::group(['middleware' => ['auth']], function () {
    Route::get('return', 'StockReturnController@index')->name('return');
    Route::group(['prefix' => 'return', 'as'=>'return.'], function () {
        Route::get('sale', 'StockReturnController@return_sale')->name('sale');
    });
   
    //Sale Return Routes
    Route::get('sale-return', 'SaleReturnController@index')->name('sale.return');
    Route::group(['prefix' => 'sale-return', 'as'=>'sale.return.'], function () {
        Route::post('datatable-data', 'SaleReturnController@get_datatable_data')->name('datatable.data');
        Route::post('store', 'SaleReturnController@store')->name('store');
        Route::get('{id}/show', 'SaleReturnController@show')->name('show');
        Route::post('delete', 'SaleReturnController@delete')->name('delete');
        Route::post('bulk-delete', 'SaleReturnController@bulk_delete')->name('bulk.delete');
    });

       
});
