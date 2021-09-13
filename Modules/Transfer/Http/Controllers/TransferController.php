<?php

namespace Modules\Transfer\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Transfer\Entities\Transfer;
use App\Http\Controllers\BaseController;

class TransferController extends BaseController
{
    public function __construct(Transfer $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('stock-transfer-access')){
            $this->setPageData('Stock Transfer Report','Stock Transfer Report','fas fa-share-square',[['name' => 'Stock Transfer Report']]);
            return view('transfer::index');
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('stock-transfer-access')){

                if (!empty($request->chalan_no)) {
                    $this->model->setChalanNo($request->chalan_no);
                }
                if (!empty($request->product_id)) {
                    $this->model->setProductID($request->product_id);
                }
                if (!empty($request->start_date)) {
                    $this->model->setStartDate($request->start_date);
                }
                if (!empty($request->end_date)) {
                    $this->model->setEndDate($request->end_date);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $product = '';
                    if(!$value->products->isEmpty())
                    {
                       $product = $this->products($value->products);
                    }
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->production->batch_no;
                    $row[] = '<a href="'.url("transfer/view/".$value->id).'" class="text-primary font-weight-bolder">'.$value->chalan_no.'</a>';
                    $row[] = date('d-M-Y',strtotime($value->transfer_date));
                    $row[] = $value->item;
                    $row[] = $product['product'];
                    $row[] = $product['unit'];
                    $row[] = $product['base_unit'];
                    $row[] = $product['qty_unit'];
                    $row[] = $product['qty_base_unit'];
                    $row[] = $product['unit_price'];
                    $row[] = $product['base_unit_price'];
                    $row[] = $product['tax'];
                    $row[] = $product['subtotal'];
                    $row[] = number_format($value->total_tax,2,'.','');
                    $row[] = number_format($value->total,2,'.','');
                    $row[] = number_format($value->shipping_cost,2,'.','');
                    $row[] = number_format($value->labor_cost,2,'.','');
                    $row[] = number_format($value->grand_total,2,'.','');
                    $data[] = $row;
                }
                return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
                $this->model->count_filtered(), $data);
            }
        }else{
            return response()->json($this->unauthorized());
        }
    }

    protected function products(object $transfered_products)
    {
        $data = [];
        $product = $unit = $base_unit = $qty_unit = $qty_base_unit = $unit_price = $base_unit_price = $tax = $subtotal =  '';         
        foreach ($transfered_products as $item) {
            $product         .= "<li class='pl-3'>".$item->name."</li>";
            $unit            .= "<li>".$item->unit->unit_name." (".$item->unit->unit_code.")"."</li>";
            $base_unit       .= "<li>".$item->base_unit->unit_name." (".$item->base_unit->unit_code.")"."</li>";
            $qty_unit        .= "<li>".number_format($item->pivot->unit_qty,2,'.','')."</li>";
            $qty_base_unit   .= "<li>".number_format($item->pivot->base_unit_qty,2,'.','')."</li>";
            $unit_price      .= "<li class='pr-3'>".number_format($item->pivot->net_unit_price,2,'.','')."</li>";
            $base_unit_price .= "<li class='pr-3'>".number_format($item->pivot->base_unit_price,2,'.','')."</li>";
            $tax             .= "<li class='pr-3'>".number_format($item->pivot->tax,2,'.','')."</li>";
            $subtotal        .= "<li class='pr-3'>".number_format($item->pivot->total,2,'.','')."</li>";
        }
        return $data = [ 
            'product'         => '<ul style="list-style:none;margin:0;padding:0;">'.$product.'</ul>',
            'unit'            => '<ul style="list-style:none;margin:0;padding:0;">'.$unit.'</ul>',
            'base_unit'       => '<ul style="list-style:none;margin:0;padding:0;">'.$base_unit.'</ul>',
            'qty_unit'        => '<ul style="list-style:none;margin:0;padding:0;">'.$qty_unit.'</ul>',
            'qty_base_unit'   => '<ul style="list-style:none;margin:0;padding:0;">'.$qty_base_unit.'</ul>',
            'unit_price'      => '<ul style="list-style:none;margin:0;padding:0;">'.$unit_price.'</ul>',
            'base_unit_price' => '<ul style="list-style:none;margin:0;padding:0;">'.$base_unit_price.'</ul>',
            'tax'             => '<ul style="list-style:none;margin:0;padding:0;">'.$tax.'</ul>',
            'subtotal'        => '<ul style="list-style:none;margin:0;padding:0;">'.$subtotal.'</ul>',
        ];   
    }

    public function show(int $id)
    {
        $transfer = $this->model->with(['production:id,batch_no','warehouse:id,name','products'])->where('warehouse_id',auth()->user()->warehouse->id)->find($id);
        if($transfer){
            $this->setPageData('Transfer Details','Transfer Details','fas fa-file',[['name' => 'Transfer Details']]);
            return view('transfer::details',compact('transfer'));
        }else{
            return $this->access_blocked();
        }
    }
}
