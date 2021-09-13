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
    Route::get('transfer', 'TransferController@index')->name('transfer');
    Route::group(['prefix' => 'transfer', 'as'=>'transfer.'], function () {
        Route::post('datatable-data', 'TransferController@get_datatable_data')->name('datatable.data');
        Route::get('view/{id}', 'TransferController@show')->name('view');
    });
});
