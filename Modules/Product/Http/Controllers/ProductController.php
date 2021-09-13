<?php

namespace Modules\Product\Http\Controllers;

use Keygen\Keygen;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\Category;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Material\Entities\Material;
use App\Http\Controllers\BaseController;
use Modules\Product\Http\Requests\ProductFormRequest;

class ProductController extends BaseController
{
    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('product-access')){
            $this->setPageData('Product Manage','Product Manage','fas fa-box',[['name' => 'Product Manage']]);
            $data = [
                'categories' => Category::allProductCategories(),
            ];

            return view('product::index',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('product-access')){

                if (!empty($request->name)) {
                    $this->model->setName($request->name);
                }
                if (!empty($request->category_id)) {
                    $this->model->setCategoryID($request->category_id);
                }
 
                if (!empty($request->product_type)) {
                    $this->model->setProductType($request->product_type);
                }
                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    // dd($value);
                    $no++;
                    if($value->unit->operator == '*'){
                        $unit_qty = $value->warehouse_product->total_qty / $value->unit->operation_value;
                    }else{
                        $unit_qty = $value->warehouse_product->total_qty * $value->unit->operation_value;
                    }
                    $row = [];
                    $row[] = $no;
                    $row[] = $this->image(PRODUCT_IMAGE_PATH,$value->image,$value->name);
                    $row[] = $value->name;
                    $row[] = $value->code;
                    $row[] = PRODUCT_TYPE_LABEL[$value->product_type];
                    $row[] = $value->category->name;
                    $row[] = $value->unit->unit_name.' ('.$value->unit->unit_code.')';
                    $row[] = $value->base_unit->unit_name.' ('.$value->base_unit->unit_code.')';
                    $row[] = number_format($value->unit_mrp,2,'.','');
                    $row[] = number_format($value->unit_price,2,'.','');
                    $row[] = number_format($value->base_unit_mrp,2,'.','');
                    $row[] = number_format($value->base_unit_price,2,'.','');
                    $row[] = number_format($unit_qty,4,'.','');
                    $row[] = $value->warehouse_product->total_qty;
                    $row[] = $value->warehouse_product->total_qty > 0 ? '<span class="label label-success label-pill label-inline" style="min-width: 70px;">Available</span>' : '<span class="label label-danger label-pill label-inline" style="min-width: 70px;">Out of Stock</span>';
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
