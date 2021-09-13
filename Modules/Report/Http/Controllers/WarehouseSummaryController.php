<?php

namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;

class WarehouseSummaryController extends BaseController
{
    public function index()
    {
        if(permission('warehouse-summary-access')){
            $this->setPageData('Warehouse Summary','Warehouse Summary','fas fa-file',[['name' => 'Warehouse Summary']]);
            return view('report::warehouse-summary-report.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function summary_data(Request $request)
    {
        if($request->ajax())
        {

            $warehouse_id = auth()->user()->warehouse->id;
            $start_date = $request->start_date;
            $end_date   = $request->end_date;
            
            $total_stock_value = DB::table('transfers')->where('warehouse_id',$warehouse_id)->sum('grand_total');
            
            $product_sale_data= DB::table('sales')
                                ->select(DB::raw("SUM(grand_total) as product_sales_grand_value"),
                                DB::raw("SUM(paid_amount) as sales_collection_received_value"),
                                DB::raw("SUM(due_amount) as product_sales_due_value"),
                                DB::raw("SUM(order_discount) as customer_discount_grand_value")
                                )
                                ->where('warehouse_id',$warehouse_id)
                                ->whereDate('sale_date','>=',$start_date)
                                ->whereDate('sale_date','<=',$end_date)
                                ->groupBy('warehouse_id')
                                ->first();

            // DB::select('SELECT customer_id, SUM(total_due)
            // FROM
            //     (SELECT customer_id, MAX(id) as last_due,due_amount as total_due
            //     FROM sales
            //     WHERE warehouse_id = ? AND due_amount > 0
            //     GROUP BY customer_id) as due_amount
            // GROUP BY customer_id', [$warehouse_id]);

            $total_due_grand_values = DB::table('sales')
            ->selectRaw('customer_id,due_amount,max(id) as last_due_id')
            ->groupBy('customer_id')
            ->where([['warehouse_id',$warehouse_id],['due_amount','>',0]])
            ->get();
            
            $total_damage_value = DB::table('sale_returns')
                                ->where('warehouse_id',$warehouse_id)
                                ->sum('grand_total');

            $collection_transfer_value = DB::table('daily_closings')
                                        ->where('warehouse_id',$warehouse_id)
                                        ->sum('transfer');

            $coupon_data = DB::table('received_coupons as rc')
                            ->leftJoin('production_coupons as pc','rc.coupon_id','=','pc.id')
                            ->leftJoin('production_products as pp','pc.production_product_id','=','pp.id')
                            ->leftJoin('salesmen as s','rc.salesmen_id','=','s.id')
                            ->select(DB::raw("SUM(pp.coupon_price) as coupon_payment_grand_value"),
                            DB::raw("COUNT(rc.id) as total_coupon_received"))
                            ->where([['s.warehouse_id',$warehouse_id],['pc.status',1]])
                            ->groupBy('s.warehouse_id')
                            ->first();

            $warehouse_expense = DB::table('expenses')
                                ->where('warehouse_id',$warehouse_id)
                                ->sum('amount');

            $data = [
                'total_stock_value' => $total_stock_value,
                'product_sale_data' => $product_sale_data,
                'total_damage_value' => $total_damage_value,
                'collection_transfer_value' => $collection_transfer_value,
                'coupon_data' => $coupon_data,
                'warehouse_expense' => $warehouse_expense,
                'total_due_grand_values' => $total_due_grand_values
            ];
            // dd($data);
            return view('report::warehouse-summary-report.summary-data',$data)->render();
        }
    }
}
