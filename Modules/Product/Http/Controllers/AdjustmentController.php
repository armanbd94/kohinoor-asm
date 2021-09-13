<?php

namespace Modules\Product\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use App\Http\Controllers\BaseController;
use Modules\Product\Entities\Adjustment;
use Modules\Product\Entities\AdjustmentProduct;
use Modules\Product\Entities\WarehouseProduct;
use Modules\Product\Http\Requests\AdjustmentFormRequest;


class AdjustmentController extends BaseController
{

    public function __construct(Adjustment $model)
    {
        $this->model = $model;
    }
    
    public function index()
    {
        if(permission('adjustment-access')){
            $this->setPageData('Manage Adjustment','Manage Adjustment','fas fa-shopping-cart',[['name' => 'Manage Adjustment']]);
            $warehouses = DB::table('warehouses')->where('status',1)->pluck('name','id');
            return view('product::adjustment.index',compact('warehouses'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('adjustment-access')){

                if (!empty($request->adjustment_no)) {
                    $this->model->setAdjustmentNo($request->adjustment_no);
                }
                if (!empty($request->warehouse_id)) {
                    $this->model->setWarehouseID($request->warehouse_id);
                }
                if (!empty($request->from_date)) {
                    $this->model->setFromDate($request->from_date);
                }
                if (!empty($request->to_date)) {
                    $this->model->setToDate($request->to_date);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('adjustment-edit')){
                        $action .= ' <a class="dropdown-item" href="'.route("adjustment.edit",$value->id).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }

                    if(permission('adjustment-view')){
                        $action .= ' <a class="dropdown-item view_data" href="'.route("adjustment.view",$value->id).'">'.self::ACTION_BUTTON['View'].'</a>';
                    }
                    
                    if(permission('adjustment-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->adjustment_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    
                    $row = [];
                    if(permission('adjustment-bulk-delete')){
                        $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                    }
                    $products = '';
                    if(!$value->products->isEmpty())
                    {
                        $products .= '<ul style="list-style:none;margin:0;padding:0;">';
                        foreach ($value->products as $product) {
                            $products .= "<li class='text-left mb-3'>$product->name <span class='badge badge-primary float-right'>". $product->pivot->base_unit_qty." </span></li>";
                        }
                        $products .= '</ul>';
                    }
                    $row[] = $no;
                    $row[] = $value->adjustment_no;
                    $row[] = $value->warehouse->name;
                    $row[] = $value->item;
                    $row[] = $products;
                    $row[] = number_format($value->total_qty,2,'.','');
                    $row[] = number_format($value->grand_total,2,'.','');
                    $row[] = $value->created_by;
                    $row[] = date(config('settings.date_format'),strtotime($value->created_at));
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
        if(permission('adjustment-add')){
            $this->setPageData('Add Adjustment','Add Adjustment','fas fa-adjust',[['name' => 'Add Adjustment']]);
            $data = [
                'adjustment_no' => 'ADJ-'.date('my').rand(1,999),
                'warehouses'    => DB::table('warehouses')->where('status',1)->pluck('name','id')
            ];
            return view('product::adjustment.create',$data);
        }else{
            return $this->access_blocked();
        }
        
    }

    public function store(AdjustmentFormRequest $request)
    {
        if($request->ajax()){
            if(permission('adjustment-add')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $adjustment_data = [
                        'adjustment_no' => $request->adjustment_no,
                        'warehouse_id'  => $request->warehouse_id,
                        'item'          => $request->item,
                        'total_qty'     => $request->total_qty,
                        'total_tax'     => $request->total_tax,
                        'grand_total'   => $request->grand_total,
                        'note'          => $request->note,
                        'created_by'    => auth()->user()->name
                    ];
                    $adjustment  = $this->model->create($adjustment_data);

                    $products = [];
                    if($request->has('products'))
                    {
                        foreach ($request->products as $value) {
                            $products[] = [
                                'adjustment_id'   => $adjustment->id,
                                'product_id'      => $value['id'],
                                'batch_no'        => $value['batch_no'],
                                'base_unit_id'    => $value['base_unit_id'],
                                'base_unit_qty'   => $value['base_unit_qty'],
                                'base_unit_price' => $value['base_unit_price'],
                                'tax_rate'        => $value['tax_rate'],
                                'tax'             => $value['tax'],
                                'total'           => $value['subtotal'],
                                'created_at'      => date('Y-m-d')
                            ];

                            $warehouse_product = WarehouseProduct::where([
                                ['warehouse_id', $request->warehouse_id],
                                ['batch_no', $value['batch_no']],
                                ['product_id', $value['id']],
                            ])->first();
                            if ($warehouse_product) {
                                $warehouse_product->qty += $value['base_unit_qty'];
                                $warehouse_product->update();
                            } else {
                                WarehouseProduct::create([
                                    'batch_no'     => $value['batch_no'],
                                    'warehouse_id' => $request->warehouse_id,
                                    'product_id'   => $value['id'],
                                    'qty'          => $value['base_unit_qty'],
                                ]);
                            }

                        }
                        if(count($products) > 0)
                        {
                            AdjustmentProduct::insert($products);
                        }

                    }
                    $output  = $this->store_message($adjustment, null);
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollback();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }


    public function show(int $id)
    {
        if(permission('adjustment-view')){
            $this->setPageData('Adjustment Details','Adjustment Details','fas fa-file',[['name'=>'Adjustment','link' => route('adjustment')],['name' => 'Adjustment Details']]);
            $adjustment = $this->model->with(['warehouse:id,name','products'])->find($id);
            return view('product::adjustment.details',compact('adjustment'));
        }else{
            return $this->access_blocked();
        }
    }
    public function edit(int $id)
    {

        if(permission('adjustment-edit')){
            $this->setPageData('Edit Adjustment','Edit Adjustment','fas fa-edit',[['name'=>'Adjustment','link' => route('adjustment')],['name' => 'Edit Adjustment']]);
            $data = [
                'adjustment'   => $this->model->with('products')->find($id),
                'warehouses'    => DB::table('warehouses')->where('status',1)->pluck('name','id')
            ];
            return view('product::adjustment.edit',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function update(AdjustmentFormRequest $request)
    {
        if($request->ajax()){
            if(permission('adjustment-edit')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $adjustmentData = $this->model->with('products')->find($request->update_id);

                    $adjustment_data = [
                        'warehouse_id' => $request->warehouse_id,
                        'item'         => $request->item,
                        'total_qty'    => $request->total_qty,
                        'total_tax'    => $request->total_tax,
                        'grand_total'  => $request->grand_total,
                        'note'         => $request->note,
                        'updated_at'   => date('Y-m-d'),
                        'modified_by'  => auth()->user()->name
                    ];

                    if(!$adjustmentData->products->isEmpty())
                    {
                        foreach ($adjustmentData->products as  $adjustment_product) {
                            $warehouse_product = WarehouseProduct::where([
                                ['warehouse_id', $adjustmentData->warehouse_id],
                                ['batch_no', $adjustment_product->pivot->batch_no],
                                ['product_id', $adjustment_product->id],
                            ])->first();
                            if ($warehouse_product) {
                                $warehouse_product->qty -= $adjustment_product->pivot->base_unit_qty;
                                $warehouse_product->update();
                            }
                        }
                    }

                    $products = [];
                    if($request->has('products'))
                    {
                        foreach ($request->products as $key => $value) {
                            $products[$value['id']] = [
                                'batch_no'        => $value['batch_no'],
                                'base_unit_id'    => $value['base_unit_id'],
                                'base_unit_qty'   => $value['base_unit_qty'],
                                'base_unit_price' => $value['base_unit_price'],
                                'tax_rate'        => $value['tax_rate'],
                                'tax'             => $value['tax'],
                                'total'           => $value['subtotal']
                            ];

                            $warehouse_product = WarehouseProduct::where([
                                ['warehouse_id', $request->warehouse_id],
                                ['batch_no', $value['batch_no']],
                                ['product_id', $value['id']],
                            ])->first();
                            if ($warehouse_product) {
                                $warehouse_product->qty += $value['base_unit_qty'];
                                $warehouse_product->update();
                            } else {
                                WarehouseProduct::create([
                                    'batch_no'     => $value['batch_no'],
                                    'warehouse_id' => $request->warehouse_id,
                                    'product_id'   => $value['id'],
                                    'qty'          => $value['base_unit_qty'],
                                ]);
                            }
                            
                        }
                        if(count($products) > 0)
                        {
                            $adjustmentData->products()->sync($products);
                        }
                    }
                    $adjustment = $adjustmentData->update($adjustment_data);
                    $output  = $this->store_message($adjustment, $request->update_id);
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollback();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function delete(Request $request)
    {
        if($request->ajax()){
            if(permission('adjustment-delete')){
                DB::beginTransaction();
                try {
                    $adjustmentData = $this->model->with('products')->find($request->id);
                    if(!$adjustmentData->products->isEmpty())
                    {
                        foreach ($adjustmentData->products as  $adjustment_product) {
                            $warehouse_product = WarehouseProduct::where([
                                ['warehouse_id', $adjustmentData->warehouse_id],
                                ['batch_no', $adjustment_product->pivot->batch_no],
                                ['product_id', $adjustment_product->id],
                            ])->first();
                            if ($warehouse_product) {
                                $warehouse_product->qty -= $adjustment_product->pivot->base_unit_qty;
                                $warehouse_product->update();
                            }
                        }
                        $adjustmentData->products()->detach();
                    }
                    $adjustment = $adjustmentData->delete();
                    $output = $adjustment ? ['status' => 'success','message' => 'Data has been deleted successfully'] : ['status' => 'error','message' => 'failed to delete data'];
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    $output = ['status'=>'error','message'=>$e->getMessage()];
                }
                return response()->json($output);
            }else{
                $output = $this->access_blocked();
            }
            return response()->json($output);
        }else{
            return response()->json($this->access_blocked());
        }
    }

    public function bulk_delete(Request $request)
    {
        if($request->ajax()){
            if(permission('adjustment-bulk-delete')){
                DB::beginTransaction();
                try {
                    foreach ($request->ids as $id) {
                        $adjustmentData = $this->model->with('products')->find($id);
                        if(!$adjustmentData->products->isEmpty())
                        {
                            foreach ($adjustmentData->products as  $adjustment_product) {
                                $warehouse_product = WarehouseProduct::where([
                                    ['warehouse_id', $adjustmentData->warehouse_id],
                                    ['batch_no', $adjustment_product->pivot->batch_no],
                                    ['product_id', $adjustment_product->id],
                                ])->first();
                                if ($warehouse_product) {
                                    $warehouse_product->qty -= $adjustment_product->pivot->base_unit_qty;
                                    $warehouse_product->update();
                                }
                            }
                            $adjustmentData->products()->detach();
                        }
                    }
                    $adjustment = $this->model->destroy($request->ids);
                    $output = $adjustment ? ['status' => 'success','message' => 'Data has been deleted successfully'] : ['status' => 'error','message' => 'failed to delete data'];
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    $output = ['status'=>'error','message'=>$e->getMessage()];
                }
            }else{
                $output = $this->access_blocked();
            }
            return response()->json($output);
        }else{
            return response()->json($this->access_blocked());
        }
    }

}
