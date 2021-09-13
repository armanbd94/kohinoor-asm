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
    //Closing Route
    Route::get('closing', 'ClosingReportController@index')->name('closing');
    Route::post('closing-data', 'ClosingReportController@closing_data')->name('closing.data');
    Route::post('closing/store', 'ClosingReportController@store')->name('closing.store');

    //Closing Report Route
    Route::get('closing-report', 'ClosingReportController@report')->name('closing.report');
    Route::post('closing-report/datatable-data', 'ClosingReportController@get_datatable_data')->name('closing.report.datatable.data');
    
    //Today Sales Report Route
    Route::get('today-sales-report', 'TodaySalesReportController@index')->name('today.sales.report');
    Route::post('today-sales-report/datatable-data', 'TodaySalesReportController@get_datatable_data')->name('today.sales.report.datatable.data');

    //Sales Report Route
    Route::get('sales-report', 'SalesReportController@index')->name('sales.report');
    Route::post('sales-report/datatable-data', 'SalesReportController@get_datatable_data')->name('sales.report.datatable.data');
    
    //Todays Customer Receipt Route
    Route::get('todays-customer-receipt', 'TodaysCustomerReceiptController@index')->name('todays.customer.receipt');
    Route::post('todays-customer-receipt/datatable-data', 'TodaysCustomerReceiptController@get_datatable_data')->name('todays.customer.receipt.datatable.data');
    
    //Customer Receipt Route
    Route::get('customer-receipt-list', 'CustomerReceiptController@index')->name('customer.receipt.list');
    Route::post('customer-receipt-list/datatable-data', 'CustomerReceiptController@get_datatable_data')->name('customer.receipt.list.datatable.data');

    //Salesman Wise Sales Report Route
    Route::get('salesman-wise-sales-report', 'SalesmanWiseSalesReportController@index')->name('salesman.wise.sales.report');
    Route::post('salesman-wise-sales-report/datatable-data', 'SalesmanWiseSalesReportController@get_datatable_data')->name('salesman.wise.sales.report.datatable.data');
    
    //Due Report Route
    Route::get('due-report', 'DueReportController@index')->name('due.report');
    Route::post('due-report/datatable-data', 'DueReportController@get_datatable_data')->name('due.report.datatable.data');

    //Shipping Cost Report Route
    Route::get('shipping-cost-report', 'ShippingCostReportController@index')->name('shipping.cost.report');
    Route::post('shipping-cost-report/datatable-data', 'ShippingCostReportController@get_datatable_data')->name('shipping.cost.report.datatable.data');
   
    //Collection Report Route
    Route::get('collection-report', 'CollectionReportController@index')->name('collection.report');
    Route::post('collection-report/datatable-data', 'CollectionReportController@get_datatable_data')->name('collection.report.datatable.data');

    //Product Wise Sales Report Route
    Route::get('product-wise-sales-report', 'ProductWiseSalesReportController@index')->name('product.wise.sales.report');
    Route::post('product-wise-sales-report/datatable-data', 'ProductWiseSalesReportController@get_datatable_data')->name('product.wise.sales.report.datatable.data');
   
    //Warehouse Expense Report Route
    Route::get('warehouse-expense-report', 'ExpenseReportController@index')->name('warehouse.expense.report');
    Route::post('warehouse-expense-report/datatable-data', 'ExpenseReportController@get_datatable_data')->name('warehouse.expense.report.datatable.data');
   
    //Damage Report Route
    Route::get('damage-report', 'DamageReportController@index')->name('damage.report');
    Route::post('damage-report/datatable-data', 'DamageReportController@get_datatable_data')->name('damage.report.datatable.data');
    
    //Warehouse Summary Report Route
    Route::get('warehouse-summary', 'WarehouseSummaryController@index')->name('warehouse.summary');
    Route::post('warehouse-summary/data', 'WarehouseSummaryController@summary_data')->name('warehouse.summary.data');

});
