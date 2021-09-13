<?php
namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use Modules\Report\Entities\SalesReport;

class SalesReportController extends BaseController
{
    public function __construct(SalesReport $model)
    {
        $this->model = $model;
    }
    
    public function index()
    {
        if(permission('sales-report-access')){
            $this->setPageData('Sale Manage','Sale Manage','fab fa-opencart',[['name' => 'Sale Manage']]);
            $data = [
                'salesmen'    => DB::table('salesmen')->where([['district_id',auth()->user()->district_id],['status',1]])->pluck('name','id'),
                'locations'   => DB::table('locations')->where('status', 1)->get(),
            ];
            return view('report::sales-report',$data);
        }else{
            return $this->access_blocked();
        }

    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('sales-report-access')){
                if (!empty($request->memo_no)) {
                    $this->model->setMemoNo($request->memo_no);
                }
                if (!empty($request->start_date)) {
                    $this->model->setFromDate($request->start_date);
                }
                if (!empty($request->end_date)) {
                    $this->model->setToDate($request->end_date);
                }
                if (!empty($request->salesmen_id)) {
                    $this->model->setSalesmenID($request->salesmen_id);
                }
                if (!empty($request->customer_id)) {
                    $this->model->setCustomerID($request->customer_id);
                }
                if (!empty($request->area_id)) {
                    $this->model->setAreaID($request->area_id);
                }
                if (!empty($request->upazila_id)) {
                    $this->model->setUpazilaID($request->upazila_id);
                }
                if (!empty($request->route_id)) {
                    $this->model->setRouteID($request->route_id);
                }


                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                $total_item = $total_qty = 0;
                foreach ($list as $value) {
                    $no++;

                    $product = '';
                    if($value->id)
                    {
                       $product = $this->products($value->id);
                    }
                    $total_item += $value->item;
                    $total_qty  += $value->total_qty;
                    $row = [];
                    $row[] = $no;
                    $row[] = date(config('settings.date_format'),strtotime($value->sale_date));
                    $row[] = $value->salesmen_name;
                    $row[] = $value->memo_no;
                    $row[] = $value->route_name;
                    $row[] = $value->area_name;
                    $row[] = $value->shop_name.' ( '.$value->name.')';

                    $row[] = $product['name'];
                    $row[] = $product['code'];
                    $row[] = $product['batch_no'];
                    $row[] = $product['unit'];
                    $row[] = $product['qty'];
                    $row[] = $product['price'];
                    $row[] = $product['tax'];
                    $row[] = $product['subtotal'];
                    
                    $row[] = $value->item.'('.$value->total_qty.')';
                    $row[] = number_format($value->total_price,2,'.','');
                    $row[] = number_format($value->order_tax_rate,2,'.','');
                    $row[] = number_format($value->order_tax,2,'.','');
                    $row[] = number_format($value->order_discount,2,'.','');
                    $row[] = number_format($value->labor_cost,2,'.','');
                    $row[] = number_format($value->shipping_cost,2,'.','');
                    $row[] = number_format($value->grand_total,2,'.','');
                    $row[] = number_format($value->previous_due,2,'.','');
                    $row[] = number_format($value->net_total,2,'.','');
                    $row[] = number_format($value->paid_amount,2,'.','');
                    $row[] = number_format($value->due_amount,2,'.','');
                    
                    $row[] = $value->payment_method ? SALE_PAYMENT_METHOD[$value->payment_method] : '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">N/A</span>';
                    $row[] = $value->account_id ? $value->account_name : 'N/A';
                    $row[] = $value->delivery_date ? date(config('settings.date_format'),strtotime($value->delivery_date)) : '';
                    $data[] = $row;
                }
                return [
                    "draw" => $request->input('draw'),
                    "recordsTotal" => $this->model->count_all(),
                    "recordsFiltered" => $this->model->count_filtered(),
                    "data" => $data,
                    'total_due'=> $this->model->total_customer_dues($request->start_date,$request->end_date,$request->memo_no,
                    $request->salesmen_id,$request->customer_id,$request->upazila_id,$request->route_id,$request->area_id),
                    'total_items' => $total_item.'('.$total_qty.')'
                ];
            }   
        }else{
            return response()->json($this->unauthorized());
        }
    }

    protected function products($sale_id)
    {

        $return_products = DB::table('sale_products as sp')
        ->leftjoin('products as p','sp.product_id','=','p.id')
        ->leftjoin('units as u','sp.sale_unit_id','=','u.id')
        ->select('sp.*','p.name','p.code','u.unit_name','u.unit_code')
        ->where('sp.sale_id',$sale_id)
        ->get();
        $name = $code = $batch_no = $unit = $qty = $price = $tax = $subtotal = '';         
        if($return_products)
        {
            foreach ($return_products as $item) {
                $name       .= "<li class='pl-3'>".$item->name."</li>";
                $code       .= "<li class='pl-3'>".$item->code."</li>";
                $batch_no   .= "<li class='pl-3'>".$item->batch_no."</li>";
                $unit       .= "<li>".$item->unit_name."</li>";
                $qty        .= "<li>".number_format($item->qty,2,'.','')."</li>";
                $price      .= "<li class='pr-3'>".number_format($item->net_unit_price,2,'.','')."</li>";
                $tax      .= "<li class='pr-3'>".number_format($item->tax,2,'.','')."</li>";
                $subtotal   .= "<li class='pr-3'>".number_format($item->total,2,'.','')."</li>";
            }
        }
        return [ 
            'name'  => '<ul style="list-style:none;margin:0;padding:0;">'.$name.'</ul>',
            'code'     => '<ul style="list-style:none;margin:0;padding:0;">'.$code.'</ul>',
            'batch_no' => '<ul style="list-style:none;margin:0;padding:0;">'.$batch_no.'</ul>',
            'unit'     => '<ul style="list-style:none;margin:0;padding:0;">'.$unit.'</ul>',
            'qty'      => '<ul style="list-style:none;margin:0;padding:0;">'.$qty.'</ul>',
            'price'    => '<ul style="list-style:none;margin:0;padding:0;">'.$price.'</ul>',
            'tax'    => '<ul style="list-style:none;margin:0;padding:0;">'.$tax.'</ul>',
            'subtotal' => '<ul style="list-style:none;margin:0;padding:0;">'.$subtotal.'</ul>',
        ];   
    }

}
