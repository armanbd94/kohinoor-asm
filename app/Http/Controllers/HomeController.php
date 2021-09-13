<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;

class HomeController extends BaseController
{
    public function index()
    {
        if (permission('dashboard-access')){

            $this->setPageData('Dashboard','Dashboard','fas fa-technometer');

            //Yearly Report
            $start = strtotime(date('Y').'-01-01');
            $end = strtotime(date('Y').'-12-31');

            $yearly_sale_amount = [];
            $yearly_transfer_amount = [];
            while ($start < $end) {
                $start_date  = date('Y').'-'.date('m',$start).'-01';
                $end_date  = date('Y').'-'.date('m',$start).'-31';

                $sale_amount = DB::table('sales')->whereDate('sale_date','>=',$start_date)
                ->whereDate('sale_date','<=',$end_date)->sum('grand_total');

                $transfer_amount = DB::table('transfers')->whereDate('transfer_date','>=',$start_date)
                ->whereDate('transfer_date','<=',$end_date)->sum('grand_total');

                $yearly_sale_amount[] = number_format($sale_amount,2,'.','');
                $yearly_transfer_amount[] = number_format($transfer_amount,2,'.','');
                $start = strtotime('+1 month',$start);
            }
            return view('home',compact('yearly_sale_amount','yearly_transfer_amount'));
        }else{
            return redirect('unauthorized')->with(['status'=>'error','message'=>'Unauthorized Access Blocked']);
        }
    }

    public function dashboard_data($start_date,$end_date)
    {
        if($start_date && $end_date)
        {
            $warehouse_id = auth()->user()->warehouse->id;

            $sale = DB::table('sales')
            ->where('warehouse_id',$warehouse_id)
            ->whereDate('sale_date','>=',$start_date)
            ->whereDate('sale_date','<=',$end_date)
            ->sum('grand_total');

            $transfer = DB::table('transfers')
            ->where('warehouse_id',$warehouse_id)
            ->whereDate('transfer_date','>=',$start_date)
            ->whereDate('transfer_date','<=',$end_date)
            ->sum('grand_total');

            $income = DB::table('sales')
            ->where('warehouse_id',$warehouse_id)
            ->whereDate('sale_date','>=',$start_date)
            ->whereDate('sale_date','<=',$end_date)
            ->sum('paid_amount');

            $expense = DB::table('expenses')
            ->where('warehouse_id',$warehouse_id)
            ->whereDate('date','>=',$start_date)
            ->whereDate('date','<=',$end_date)
            ->sum('amount');

            $coupon_data = DB::table('received_coupons as rc')
            ->leftJoin('production_coupons as pc','rc.coupon_id','=','pc.id')
            ->leftJoin('production_products as pp','pc.production_product_id','=','pp.id')
            ->leftJoin('salesmen as s','rc.salesmen_id','=','s.id')
            ->select(DB::raw("SUM(pp.coupon_price) as coupon_payment_grand_value"),DB::raw("COUNT(rc.id) as total_coupon_received"))
            ->where([['s.warehouse_id',$warehouse_id],['pc.status',1]])
            ->whereDate('rc.created_at','>=',$start_date)
            ->whereDate('rc.created_at','<=',$end_date)
            ->groupBy('s.warehouse_id')
            ->first();
            $total_coupon_payment = 0;
            $total_coupon_received = 0;
            if($coupon_data)
            {
                $total_coupon_payment = $coupon_data->coupon_payment_grand_value ? $coupon_data->coupon_payment_grand_value : 0;
                $total_coupon_received = $coupon_data->total_coupon_received ? $coupon_data->total_coupon_received : 0;
            }

            $data = [
                'sale'                  => $sale,
                'transfer'              => $transfer,
                'income'                => $income,
                'expense'               => $expense,
                'total_coupon_received' => $total_coupon_received,
                'total_coupon_payment'  => $total_coupon_payment,
            ];
            return response()->json($data);
        }

    }

    public function unauthorized()
    {
        $this->setPageData('Unauthorized','Unauthorized','fas fa-ban',[['name' => 'Unauthorized']]);
        return view('unauthorized');
    }
}
