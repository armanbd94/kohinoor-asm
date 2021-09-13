<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Product\Entities\Product;
use App\Http\Controllers\BaseController;
use Modules\FinishGoods\Entities\FinishGood;
use Modules\Product\Entities\ProductVariant;
use Modules\Product\Http\Requests\BarcodeFormRequest;

class BarcodeController extends BaseController
{

    public function index()
    {
        if(permission('print-barcode-access')){
            $this->setPageData('Print Barcode','Print Barcode','fas fa-barcode',[['name'=>'Product','link'=> route('product')],['name' => 'Print Barcode']]);
            return view('product::barcode.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function generateBarcode(BarcodeFormRequest $request)
    {
        if($request->ajax())
        {
            $data = [
                'barcode'           => $request->product_code,
                'product_name'      => $request->product_name,
                'product_price'     => $request->product_price,
                'barcode_symbology' => $request->barcode_symbology,
                'tax_rate'          => $request->tax_rate,
                'tax_method'        => $request->tax_method,
                'barcode_qty'       => $request->barcode_qty,
                'row_qty'           => $request->row_qty
            ];
            return view('product::barcode.print-area',$data)->render();
        }
    }

    public function autocomplete_search_product(Request $request)
    {
        if(!empty($request->search)){
            $output = array();
            $search_text = $request->search;
            $products = Product::with('tax')->where(function($q) use($search_text){
                $q->where('code', 'like','%'.$search_text.'%')
                ->orWhere('name', 'like','%'.$search_text.'%');
            })->get();

            if(!$products->isEmpty())
            {
                $temp_array = [];
                foreach ($products as $value) {
                    $temp_array['code']              = $value->code;
                    $temp_array['name']              = $value->name;
                    $temp_array['price']             = $value->base_unit_price;
                    $temp_array['barcode_symbology'] = $value->barcode_symbology;
                    $temp_array['tax_rate']          = $value->tax->rate ? $value->tax->rate : 0;
                    $temp_array['tax_method']        = $value->tax_method;
                    $temp_array['value']             = $value->name.' ('.$value->code.')';
                    $temp_array['label']             = $value->name.' ('.$value->code.')';
                    $output[] = $temp_array;
                }
            }

            if(empty($output) && count($output) == 0)
            {
                $output['value'] = '';
                $output['label'] = 'No Record Found';
            }
            return $output; 
        }
    }

    public function search_product(Request $request)
    {
        $product_data =Product::with(['tax','unit','base_unit'])->where('code',$request['data'])->first();
        if($product_data)
        {
            $product['id']             = $product_data->id;
            $product['name']           = $product_data->name;
            $product['code']           = $product_data->code;
            $product['price']          = $product_data->base_unit_price;
            $product['base_unit_id']   = $product_data->base_unit_id;
            $product['base_unit_name'] = $product_data->base_unit->unit_name.' ('.$product_data->base_unit->unit_code.')';
            $product['tax_rate']       = $product_data->tax->rate ? $product_data->tax->rate : 0;
            $product['tax_name']       = $product_data->tax->name;
            $product['tax_method']     = $product_data->tax_method;
            return $product;
        }
    }


}
