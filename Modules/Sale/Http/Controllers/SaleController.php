<?php

namespace Modules\Sale\Http\Controllers;

use Exception;
use App\Models\Tax;
use App\Models\Unit;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use Modules\Sale\Entities\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Product\Entities\Product;
use Modules\Sale\Entities\SaleProduct;
use Illuminate\Support\Facades\Session;
use Modules\Customer\Entities\Customer;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;
use Modules\Customer\Entities\CustomerGroup;
use Modules\Product\Entities\WarehouseProduct;
use Modules\Sale\Http\Requests\SaleFormRequest;
use Modules\Sale\Http\Requests\SaleDeliveryFormRequest;

class SaleController extends BaseController
{
    use UploadAble;
    public function __construct(Sale $model)
    {
        $this->model = $model;
    }
    
    public function index()
    {
        if(permission('sale-access')){
            $this->setPageData('Sale Manage','Sale Manage','fab fa-opencart',[['name' => 'Sale Manage']]);
            $data = [
                'salesmen'    => DB::table('salesmen')->where([['district_id',auth()->user()->district_id],['status',1]])->pluck('name','id'),
                'locations'   => DB::table('locations')->where('status', 1)->get(),
            ];
            return view('sale::index',$data);
        }else{
            return $this->access_blocked();
        }

    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('sale-access')){
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
                if (!empty($request->payment_status)) {
                    $this->model->setPaymentStatus($request->payment_status);
                }


                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if (permission('sale-edit')) {
                        $action .= ' <a class="dropdown-item" href="'.route("sale.edit",$value->id).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if (permission('sale-view')) {
                        $action .= ' <a class="dropdown-item view_data" href="'.route("sale.show",$value->id).'">'.self::ACTION_BUTTON['View'].'</a>';
                    }
 					if($value->document)
                    {
                        $action .= '<a class="dropdown-item" href="'.asset('storage/'.SALE_DOCUMENT_PATH.$value->document).'" download><i class="fas fa-download mr-2"></i> Document</a>';
                    }
                    if (permission('sale-delete')) {
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->memo_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    if (permission('sale-edit')) {
                        $delivery_text = $value->delivery_status ? 'Update Delivery' : 'Add Delivery';
                        $action .= ' <a class="dropdown-item add_delivery" data-id="'.$value->id.'" data-status="'.$value->delivery_status.'" data-date="'.$value->delivery_date.'" 
                        ><i class="fas fa-truck text-info mr-2"></i> '.$delivery_text.'</a>';
                    }
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->memo_no;
                    $row[] = $value->salesmen_name;
                    $row[] = $value->shop_name.' ( '.$value->name.')';
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
                    $row[] = date(config('settings.date_format'),strtotime($value->sale_date));
                    $row[] = PAYMENT_STATUS_LABEL[$value->payment_status];
                    $row[] = $value->payment_method ? SALE_PAYMENT_METHOD[$value->payment_method] : '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">N/A</span>';
                    $row[] = DELIVERY_STATUS[$value->delivery_status];
                    $row[] = $value->delivery_date ? date(config('settings.date_format'),strtotime($value->delivery_date)) : '';
                    $row[] = action_button($action);//custom helper function for action button
                    $data[] = $row;
                }
                return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
                $this->model->count_filtered(), $data);
            }   
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function create()
    {
        if(permission('sale-add')){
            $this->setPageData('Add Sale','Add Sale','fas fa-shopping-cart',[['name' => 'Add Sale']]);
            $data = [
                'taxes'       => Tax::activeTaxes(),
                'salesmen'    => DB::table('salesmen')->where([['district_id',auth()->user()->district_id],['status',1]])->pluck('name','id'),
                'locations'   => DB::table('locations')->where('status', 1)->get(),
                'memo_no'     => 'SINV-'.date('ymd').rand(1,999),
            ];
            return view('sale::create',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function store(SaleFormRequest $request)
    {
        if($request->ajax()){
            if(permission('sale-add')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $customer = Customer::with('coa')->find($request->customer_id);
                    $warehouse_id = auth()->user()->warehouse->id;
                    $sale_data = [
                        'memo_no' => $request->memo_no, 
                        'warehouse_id' =>  $warehouse_id, 
                        'district_id' => $customer->district_id, 
                        'upazila_id' => $customer->upazila_id, 
                        'route_id' => $customer->route_id, 
                        'area_id' => $customer->area_id, 
                        'salesmen_id' => $request->salesmen_id, 
                        'customer_id' => $customer->id, 
                        'item'             => $request->item,
                        'total_qty'        => $request->total_qty,
                        'total_discount'   => $request->total_discount ? $request->total_discount : 0,
                        'total_tax'        => $request->total_tax ? $request->total_tax : 0,
                        'total_price'      => $request->total_price,
                        'order_tax_rate'   => $request->order_tax_rate,
                        'order_tax'        => $request->order_tax,
                        'order_discount'   => $request->order_discount ? $request->order_discount : 0,
                        'shipping_cost'    => $request->shipping_cost ? $request->shipping_cost : 0,
                        'labor_cost'       => $request->labor_cost ? $request->labor_cost : 0,
                        'grand_total'      => $request->grand_total ,
                        'previous_due'     => $request->previous_due ? $request->previous_due : 0,
                        'net_total'        => $request->grand_total + ($request->previous_due ? $request->previous_due : 0), 
                        'paid_amount'      => $request->paid_amount ? $request->paid_amount : 0,
                        'due_amount'       => (($request->grand_total + ($request->previous_due ? $request->previous_due : 0)) - ($request->paid_amount ? $request->paid_amount : 0)),
                        'payment_status'   => $request->payment_status,
                        'payment_method'   => $request->payment_method ? $request->payment_method : null,
                        'account_id'       => $request->account_id ? $request->account_id : null,
                        'reference_no' => $request->reference_no ? $request->reference_no : null, 
                        'note' => $request->note, 
                        'sale_date' => $request->sale_date, 
                        'created_by' => auth()->user()->name
                    ];

                    //payment data for account transaction
                    $payment_data = [
                        'payment_method' => $request->payment_method ? $request->payment_method : null,
                        'account_id'     => $request->account_id ? $request->account_id : null,
                        'paid_amount'    => $request->paid_amount ? $request->paid_amount : 0,
                    ];

                    if($request->hasFile('document')){
                        $sale_data['document'] = $this->upload_file($request->file('document'),SALE_DOCUMENT_PATH);
                    }
                    $sale  = $this->model->create($sale_data);

                    $saleData = $this->model->with('sale_products')->find($sale->id);
                    //purchase products
                    $products = [];
                    $direct_cost = [];
                    if($request->has('products'))
                    {
                        foreach ($request->products as $key => $value) {
                            $unit = Unit::where('unit_name',$value['unit'])->first();
                            if($unit->operator == '*'){
                                $qty = $value['qty'] * $unit->operation_value;
                            }else{
                                $qty = $value['qty'] / $unit->operation_value;
                            }

                            $products[] = [
                                'sale_id'          => $sale->id,
                                'product_id'       => $value['id'],
                                'batch_no'         => $value['batch_no'],
                                'qty'              => $value['qty'],
                                'sale_unit_id'     => $unit ? $unit->id : null,
                                'net_unit_price'   => $value['net_unit_price'],
                                'discount'         => 0,
                                'tax_rate'         => $value['tax_rate'],
                                'tax'              => $value['tax'],
                                'total'            => $value['subtotal']
                            ];
                            
                            $product = DB::table('production_products as pp')
                            ->selectRaw('pp.*')
                            ->join('productions as p','pp.production_id','=','p.id')
                            ->where([
                                ['p.batch_no',$value['batch_no']],
                                ['p.warehouse_id', $warehouse_id],
                                ['pp.product_id',$value['id']],
                            ])
                            ->first();
                            if($product){
                                $direct_cost[] = $qty * ($product ? $product->per_unit_cost : 0);
                            }

                            $warehouse_product = WarehouseProduct::where([
                                ['batch_no',$value['batch_no']],
                                ['warehouse_id', $warehouse_id],
                                ['product_id',$value['id']],['qty','>',0],
                            ])->first();
                            if($warehouse_product)
                            {
                                $warehouse_product->qty -= $qty;
                                $warehouse_product->update();
                            }

                            
                        }
                        if(count($products) > 0)
                        {
                            SaleProduct::insert($products);
                        }
                    }

                    $sum_direct_cost = array_sum($direct_cost);
                    $total_tax = ($request->total_tax ? $request->total_tax : 0) + ($request->order_tax ? $request->order_tax : 0);
                    
                    if(empty($sale))
                    {
                        if($request->hasFile('document')){
                            $this->delete_file($sale_data['document'], SALE_DOCUMENT_PATH);
                        }
                    }

                    

                    $data = $this->sale_balance_add($sale->id,$request->memo_no,$request->grand_total,$total_tax,
                    $sum_direct_cost,$customer->coa->id,$customer->name,$request->sale_date,$payment_data, $warehouse_id);

                    $output  = $this->store_message($sale, $request->sale_id);
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollback();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
                return response()->json($output);
            }else{
                return response()->json($this->unauthorized());
            }
        }else{
            return response()->json($this->unauthorized());
        }
    }

 
    private function sale_balance_add(int $sale_id, $invoice_no, $grand_total, $total_tax,$sum_direct_cost, int $customer_coa_id, string $customer_name, $sale_date, array $payment_data,int $warehouse_id) {

        //Inventory Credit
        $coscr = array(
            'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', $this->coa_head_code('inventory'))->value('id'),
            'warehouse_id'        => $warehouse_id,
            'voucher_no'          => $invoice_no,
            'voucher_type'        => 'INVOICE',
            'voucher_date'        => $sale_date,
            'description'         => 'Inventory Credit For Invoice No '.$invoice_no,
            'debit'               => 0,
            'credit'              => $sum_direct_cost,
            'posted'              => 1,
            'approve'             => 1,
            'created_by'          => auth()->user()->name,
            'created_at'          => date('Y-m-d H:i:s')
        ); 

            // customer Debit
            $sale_coa_transaction = array(
            'chart_of_account_id' => $customer_coa_id,
            'warehouse_id'        => $warehouse_id,
            'voucher_no'          => $invoice_no,
            'voucher_type'        => 'INVOICE',
            'voucher_date'        => $sale_date,
            'description'         => 'Customer debit For Invoice No -  ' . $invoice_no . ' Customer ' .$customer_name,
            'debit'               => $grand_total,
            'credit'              => 0,
            'posted'              => 1,
            'approve'             => 1,
            'created_by'          => auth()->user()->name,
            'created_at'          => date('Y-m-d H:i:s')
        );

        $product_sale_income = array(
            'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', $this->coa_head_code('product_sale'))->value('id'),
            'warehouse_id'        => $warehouse_id,
            'voucher_no'          => $invoice_no,
            'voucher_type'        => 'INVOICE',
            'voucher_date'        => $sale_date,
            'description'         => 'Sale Income For Invoice NO - ' . $invoice_no . ' Customer ' .$customer_name,
            'debit'               => 0,
            'credit'              => $grand_total,
            'posted'              => 1,
            'approve'             => 1,
            'created_by'          => auth()->user()->name,
            'created_at'          => date('Y-m-d H:i:s')
        ); 

        Transaction::insert([
            $coscr, $sale_coa_transaction, $product_sale_income
        ]);

        if($total_tax){
            $tax_info = array(
                'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', $this->coa_head_code('tax'))->value('id'),
                'warehouse_id'        => $warehouse_id,
                'voucher_no'          => $invoice_no,
                'voucher_type'        => 'INVOICE',
                'voucher_date'        => $sale_date,
                'description'         => 'Sale Total Tax For Invoice NO - ' . $invoice_no . ' Customer ' .$customer_name,
                'debit'               => $total_tax,
                'credit'              => 0,
                'posted'              => 1,
                'approve'             => 1,
                'created_by'          => auth()->user()->name,
                'created_at'          => date('Y-m-d H:i:s')
            ); 
            Transaction::create($tax_info);
        }
        

        if(!empty($payment_data['paid_amount']))
        {
        
            /****************/
            $customer_credit = array(
                'chart_of_account_id' => $customer_coa_id,
                'warehouse_id'        => $warehouse_id,
                'voucher_no'          => $invoice_no,
                'voucher_type'        => 'INVOICE',
                'voucher_date'        => $sale_date,
                'description'         => 'Customer credit for Paid Amount For Customer Invoice NO- ' . $invoice_no . ' Customer- ' . $customer_name,
                'debit'               => 0,
                'credit'              => $payment_data['paid_amount'],
                'posted'              => 1,
                'approve'             => 1,
                'created_by'          => auth()->user()->name,
                'created_at'          => date('Y-m-d H:i:s')
            );
            if($payment_data['payment_method'] == 1){
                //Cah In Hand debit
                $payment = array(
                    'chart_of_account_id' => $payment_data['account_id'],
                    'warehouse_id'        => $warehouse_id,
                    'voucher_no'          => $invoice_no,
                    'voucher_type'        => 'INVOICE',
                    'voucher_date'        => $sale_date,
                    'description'         => 'Cash in Hand in Sale for Invoice No - ' . $invoice_no . ' customer- ' .$customer_name,
                    'debit'               => $payment_data['paid_amount'],
                    'credit'              => 0,
                    'posted'              => 1,
                    'approve'             => 1,
                    'created_by'          => auth()->user()->name,
                    'created_at'          => date('Y-m-d H:i:s')
                );
            }else{
                // Bank Ledger
                $payment = array(
                    'chart_of_account_id' => $payment_data['account_id'],
                    'warehouse_id'        => $warehouse_id,
                    'voucher_no'          => $invoice_no,
                    'voucher_type'        => 'INVOICE',
                    'voucher_date'        => $sale_date,
                    'description'         => 'Paid amount for customer  Invoice No - ' . $invoice_no . ' customer -' . $customer_name,
                    'debit'               => $payment_data['paid_amount'],
                    'credit'              => 0,
                    'posted'              => 1,
                    'approve'             => 1,
                    'created_by'          => auth()->user()->name,
                    'created_at'          => date('Y-m-d H:i:s')
                );
            }
            Transaction::insert([$customer_credit,$payment]);
            
        }
    }


    public function show(int $id)
    {
        if(permission('sale-view')){
            $this->setPageData('Sale Details','Sale Details','fas fa-file',[['name'=>'Sale','link' => route('sale')],['name' => 'Sale Details']]);
            $sale = $this->model->with('sale_products','customer','salesmen')->find($id);
            return view('sale::details',compact('sale'));
        }else{
            return $this->access_blocked();
        }
    }
    public function edit(int $id)
    {
        if(permission('sale-edit')){
            $this->setPageData('Edit Sale','Edit Sale','fas fa-edit',[['name'=>'Sale','link' => route('sale')],['name' => 'Edit Sale']]);
            $data = [
                'sale'      => $this->model->with('sale_products','customer','salesmen','route','area')->find($id),
                'taxes'      => Tax::activeTaxes(),
                'customer_groups' => CustomerGroup::where('status',1)->get(),
            ];
            return view('sale::edit',$data);
        }else{
            return $this->access_blocked();
        }

    }

    public function update(SaleFormRequest $request)
    {
        if($request->ajax()){
            if(permission('sale-edit')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $warehouse_id = auth()->user()->warehouse->id;
                    $sale_data = [
                        'item'           => $request->item,
                        'total_qty'      => $request->total_qty,
                        'total_discount' => $request->total_discount ? $request->total_discount : 0,
                        'total_tax'      => $request->total_tax ? $request->total_tax : 0,
                        'total_price'    => $request->total_price,
                        'order_tax_rate' => $request->order_tax_rate,
                        'order_tax'      => $request->order_tax,
                        'order_discount' => $request->order_discount ? $request->order_discount : 0,
                        'shipping_cost'  => $request->shipping_cost ? $request->shipping_cost : 0,
                        'labor_cost'     => $request->labor_cost ? $request->labor_cost : 0,
                        'grand_total'    => $request->grand_total,
                        'previous_due'   => $request->previous_due ? $request->previous_due : 0,
                        'net_total'      => $request->grand_total + ($request->previous_due ? $request->previous_due : 0),
                        'paid_amount'    => $request->paid_amount ? $request->paid_amount : 0,
                        'due_amount'     => (($request->grand_total + ($request->previous_due ? $request->previous_due : 0)) - ($request->paid_amount ? $request->paid_amount : 0)),
                        'payment_status' => $request->payment_status,
                        'payment_method' => $request->payment_method ? $request->payment_method : null,
                        'account_id'     => $request->account_id ? $request->account_id : null,
                        'reference_no'   => $request->reference_no ? $request->reference_no : null,
                        'note'           => $request->note,
                        'sale_date'      => $request->sale_date,
                        'updated_by'     => auth()->user()->name
                    ];

                    $payment_data = [
                        'payment_method' => $request->payment_method ? $request->payment_method : null,
                        'account_id'     => $request->account_id ? $request->account_id : null,
                        'paid_amount'    => $request->paid_amount ? $request->paid_amount : 0,
                    ];

                    if($request->hasFile('document')){
                        $sale_data['document'] = $this->upload_file($request->file('document'),SALE_DOCUMENT_PATH);
                    }
                    $saleData = $this->model->with('sale_products')->find($request->sale_id);
                    
                    $old_document = $saleData ? $saleData->document : '';

                    if(!$saleData->sale_products->isEmpty())
                    {
                        foreach ($saleData->sale_products as  $sale_product) {
                            $sold_qty = $sale_product->pivot->qty ? $sale_product->pivot->qty : 0;
                            $sale_unit = Unit::find($sale_product->pivot->sale_unit_id);
                            if($sale_unit->operator == '*'){
                                $sold_qty = $sold_qty * $sale_unit->operation_value;
                            }else{
                                $sold_qty = $sold_qty / $sale_unit->operation_value;
                            }

                            $warehouse_product = WarehouseProduct::where([
                                ['batch_no',$sale_product->pivot->batch_no],
                                ['warehouse_id', $saleData->warehouse_id],
                                ['product_id',$sale_product->pivot->product_id]
                            ])->first();
                            if($warehouse_product)
                            {
                                $warehouse_product->qty += $sold_qty;
                                $warehouse_product->update();
                            }
                        }
                        SaleProduct::where('sale_id',$request->sale_id)->delete();
                    }
                    
                    $products = [];
                    $direct_cost  = [];
                    if($request->has('products'))
                    {
                        foreach ($request->products as $key => $value) {
                            $unit = Unit::where('unit_name',$value['unit'])->first();
                            if($unit->operator == '*'){
                                $qty = $value['qty'] * $unit->operation_value;
                            }else{
                                $qty = $value['qty'] / $unit->operation_value;
                            }
                            $products[] = [
                                'sale_id'          => $saleData->id,
                                'product_id'       => $value['id'],
                                'batch_no'         => $value['batch_no'],
                                'qty'              => $value['qty'],
                                'sale_unit_id'     => $unit ? $unit->id : null,
                                'net_unit_price'   => $value['net_unit_price'],
                                'discount'         => 0,
                                'tax_rate'         => $value['tax_rate'],
                                'tax'              => $value['tax'],
                                'total'            => $value['subtotal']
                            ];
                            
                            $product = DB::table('production_products as pp')
                            ->selectRaw('pp.*')
                            ->join('productions as p','pp.production_id','=','p.id')
                            ->where([
                                ['p.batch_no',$value['batch_no']],
                                ['p.warehouse_id', $warehouse_id],
                                ['pp.product_id',$value['id']],
                            ])
                            ->first();
                            if($product){
                                $direct_cost[] = $qty * ($product ? $product->per_unit_cost : 0);
                            }

                            $warehouse_product = WarehouseProduct::where([
                                ['batch_no',$value['batch_no']],
                                ['warehouse_id', $warehouse_id],
                                ['product_id',$value['id']],['qty','>',0],
                            ])->first();
                            if($warehouse_product)
                            {
                                $warehouse_product->qty -= $qty;
                                $warehouse_product->update();
                            }
                            
                        }
                        if(count($products) > 0)
                        {
                            SaleProduct::insert($products);
                        }
                    }
                    $customer = Customer::with('coa')->find( $saleData->customer_id);
                    $sale = $saleData->update($sale_data);
                    $sum_direct_cost = array_sum($direct_cost);
                    $total_tax = ($request->total_tax ? $request->total_tax : 0) + ($request->order_tax ? $request->order_tax : 0);
                    
                    if($sale && $old_document != '')
                    {
                        $this->delete_file($old_document,SALE_DOCUMENT_PATH);
                    }
                    

                    Transaction::where(['voucher_no'=>$request->memo_no,'voucher_type'=>'INVOICE'])->delete();
                    
                    $this->sale_balance_add($request->sale_id,$request->memo_no,$request->grand_total,$total_tax,$sum_direct_cost,$customer->coa->id,$customer->name,$request->sale_date,$payment_data,$warehouse_id);
                    $output  = $this->store_message($sale, $request->sale_id);
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollback();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
                return response()->json($output);
            }else{
                return response()->json($this->unauthorized());
            }
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function delete(Request $request)
    {
        if($request->ajax()){
            if(permission('sale-delete'))
            {
                DB::beginTransaction();
                try {
    
                    $saleData = $this->model->with('sale_products')->find($request->id);
                    $old_document = $saleData ? $saleData->document : '';
    
                    if(!$saleData->sale_products->isEmpty())
                    {
                        
                        foreach ($saleData->sale_products as  $sale_product) {
                            $sold_qty = $sale_product->pivot->qty ? $sale_product->pivot->qty : 0;
                            $sale_unit = Unit::find($sale_product->pivot->sale_unit_id);
                            if($sale_unit->operator == '*'){
                                $sold_qty = $sold_qty * $sale_unit->operation_value;
                            }else{
                                $sold_qty = $sold_qty / $sale_unit->operation_value;
                            }

                            $warehouse_product = WarehouseProduct::where([
                                ['batch_no',$sale_product->pivot->batch_no],
                                ['warehouse_id', $saleData->warehouse_id],
                                ['product_id',$sale_product->pivot->product_id]
                            ])->first();
                            if($warehouse_product)
                            {
                                $warehouse_product->qty += $sold_qty;
                                $warehouse_product->update();
                            }
                            
                            
                        }
                        SaleProduct::where('sale_id',$saleData->id)->delete();
                    }
                    Transaction::where(['voucher_no'=>$saleData->memo_no,'voucher_type'=>'INVOICE'])->delete();
    
                    $result = $saleData->delete();
                    if($result)
                    {
                        if($old_document != '')
                        {
                            $this->delete_file($old_document,SALE_DOCUMENT_PATH);
                        }
                        $output = ['status' => 'success','message' => 'Data has been deleted successfully'];
                    }else{
                        $output = ['status' => 'error','message' => 'Failed to delete data'];
                    }
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollback();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
                return response()->json($output);
            }else{
                return response()->json($this->unauthorized());
            }
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function delivery_status_update(SaleDeliveryFormRequest $request)
    {
        $result = $this->model->find($request->sale_id)->update([
            'delivery_status' => $request->delivery_status,
            'delivery_date'   => $request->delivery_date,
        ]);
        $output  = $this->store_message($result, $request->sale_id);
        return response()->json($output);
    }

}
