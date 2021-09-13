<?php

namespace Modules\Stock\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use App\Http\Controllers\BaseController;
use Modules\Product\Entities\WarehouseProduct;

class ProductStockController extends BaseController
{

    public function __construct(WarehouseProduct $model)
    {
        $this->model = $model;
    }
    public function index()
    {
        if(permission('product-stock-report-access')){
            $this->setPageData('Product Stock Report','Product Stock Report','fas fa-boxes',[['name' => 'Product Stock Report']]);
            $data = [
                'warehouses' => DB::table('warehouses')->where('status',1)->pluck('name','id')
            ];
            return view('stock::product.index',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function get_product_stock_data(Request $request)
    {
        if($request->ajax())
        {
            $warehouse_id = auth()->user()->warehouse->id;
            $product_id   = $request->product_id;

            $warehouses = Warehouse::with('products')
            ->whereHas('products',function($q) use ($product_id){
                $q->where([['product_id',$product_id],['qty','>',0]]);
            })
            ->where([['id',$warehouse_id],['status',1]])
            ->get();

            $product = Product::select('id','name')->find($request->product_id);

            return view('stock::product.product-list',compact('warehouses','product_id','product'))->render();
        }
    }

    public function product_search(Request $request)
    {
        if ($request->ajax()) {
            if(!empty($request->search)){
                $data = Product::toBase()->where('name', 'like','%'.$request->search.'%')->orWhere('code', 'like','%'.$request->search.'%')->get();
                $output = array();
                if(!empty($data) && count($data) > 0)
                {
                    foreach($data as $row)
                    {
                        $temp_array             = array();
                        $temp_array['id']       = $row->id;
                        $temp_array['value']    = $row->name;
                        $temp_array['label']    = $row->name;
                        $output[]               = $temp_array;
                    }
                } else{
                    $output['value']            = '';
                    $output['label']            = 'No Record Found';
                }
                return $output; 
            }
        }
    }
}
