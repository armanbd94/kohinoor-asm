<?php

namespace Modules\Sale\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\WarehouseProduct;

class ProductController extends Controller
{
    public function product_autocomplete_search(Request $request)
    {
        if($request->ajax())
        {
            if(!empty($request->search))
            {
                $output = [];
                $search_text = $request->search;
                $data =  WarehouseProduct::with('product')
                ->groupBy('product_id','batch_no')
                ->where([
                    [ 'warehouse_id',auth()->user()->warehouse->id],['qty','>',0]
                ])->where(function($subQuery) use ($search_text)
                {   
                    $subQuery->whereHas('product', function ($query) use ($search_text){
                        $query->where('name','like','%'.$search_text.'%')
                        ->orWhere('code','like','%'.$search_text.'%');
                    });
                })
                ->orderBy('product_id','asc')
                ->orderBy('batch_no','asc')
                ->get();
                

                if(!$data->isEmpty())
                {
                    foreach ($data as $value) {
                        $item['id']       = $value->product->id;
                        $item['code']     = $value->product->code;
                        $item['batch_no'] = $value->batch_no;
                        $item['label']    = $value->batch_no.' - '.$value->product->name.' ('.$value->product->code.') - [Stock Avl. Qty: '.$value->qty.']';
                        $output[]         = $item;
                    }
                }else{
                    $output['code'] = '';
                    $output['batch_no'] = '';
                    $output['label'] = 'No Record Found';
                }
                return $output;
            }
        }
    }

    public function product_search(Request $request)
    {
        if($request->ajax())
        {

            $product = DB::table('warehouse_product as wp')
            ->join('products as p','wp.product_id','=','p.id')
            ->leftjoin('taxes as t','p.tax_id','=','t.id')
            ->where([
                ['wp.warehouse_id',auth()->user()->warehouse->id],
                ['wp.product_id',(int)$request->data['id']],
                ['wp.batch_no',$request->data['batch_no']]
            ])
            ->selectRaw('wp.*,p.name,p.code,p.base_unit_id,p.base_unit_price as price,p.tax_method,t.name as tax_name,t.rate as tax_rate')
            ->first();

            if($product)
            {
                $output['id']         = $product->product_id;
                $output['name']       = $product->name;
                $output['code']       = $product->code;
                $output['price']      = $product->price;
                $output['qty']        = $product->qty;
                $output['batch_no']   = $product->batch_no;
                $output['tax_name']   = $product->tax_name ?? 'No Tax';
                $output['tax_rate']   = $product->tax_rate ?? 0;
                $output['tax_method'] = $product->tax_method;

                $units = Unit::where('base_unit',$product->base_unit_id)->orWhere('id',$product->base_unit_id)->get();
                $unit_name            = [];
                $unit_operator        = [];
                $unit_operation_value = [];
                if($units)
                {
                    foreach ($units as $unit) {
                        if($product->base_unit_id == $unit->id)
                        {
                            array_unshift($unit_name,$unit->unit_name);
                            array_unshift($unit_operator,$unit->operator);
                            array_unshift($unit_operation_value,$unit->operation_value);
                        }else{
                            $unit_name           [] = $unit->unit_name;
                            $unit_operator       [] = $unit->operator;
                            $unit_operation_value[] = $unit->operation_value;
                        }
                    }
                }
                $output['unit_name'] = implode(',',$unit_name).',';
                $output['unit_operator'] = implode(',',$unit_operator).',';
                $output['unit_operation_value'] = implode(',',$unit_operation_value).',';
                return $output;
            }
        }
    }
}
