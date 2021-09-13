<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Product\Entities\WarehouseProduct;

class InventoryController extends BaseController
{
    public function __construct(WarehouseProduct $model)
    {
        $this->model = $model;
    }


    public function index()
    {
        if(permission('inventory-report-access')){
            $this->setPageData('Inventory Report','Inventory Report','fas fa-boxes',[['name'=>'Inventory Report']]);
            return view('inventory.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('inventory-report-access')){

                if (!empty($request->batch_no)) {
                    $this->model->setBatchNo($request->batch_no);
                }
                if (!empty($request->product_id)) {
                    $this->model->setProductID($request->product_id);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->name;
                    $row[] = $value->batch_no;
                    $row[] = $value->unit_name;
                    $row[] = number_format($value->qty,2,'.','');
                    $row[] = number_format($value->base_unit_price,2,'.','');
                    $row[] = number_format(($value->qty * $value->base_unit_price),2,'.','');
                    $row[] = $value->qty > 0 ? '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Available</span>' : '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Out of Stock</span>';
                   $data[] = $row;
                }
                return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
                $this->model->count_filtered(), $data);
            }
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
