<?php

namespace Modules\StockReturn\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Sale\Entities\Sale;
use App\Http\Controllers\BaseController;

class StockReturnController extends BaseController
{
    public function index()
    {
        if(permission('return-access')){
            $this->setPageData('Sale Return','Sale Return','fas fa-undo-alt',[['name' => 'Sale Return']]);
            return view('stockreturn::form');
        }else{
            return $this->access_blocked();
        }
    }

    public function return_sale(Request $request)
    {
        if(permission('sale-return-access')){
            $sale = Sale::with(['sale_products','customer:id,name,shop_name','warehouse:id,name','salesmen:id,name,phone','route:id,name','area:id,name'])
            ->where([['memo_no',$request->get('memo_no')],['warehouse_id',auth()->user()->warehouse->id]])->first();

            if($sale){
                $this->setPageData('Sale Return','Sale Return','fas fa-undo-alt',[['name' => 'Sale Return']]);
                $data = [
                    'sale'=>$sale,
                ];
                return view('stockreturn::sale.edit',$data);
            }else{
                return redirect('return')->with(['status'=>'error','message'=>'No Data Found']);
            }
        }else{
            return $this->access_blocked();
        }
    }

    
}
