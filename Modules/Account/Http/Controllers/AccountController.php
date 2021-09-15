<?php

namespace Modules\Account\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\ChartOfAccount;

class AccountController extends BaseController
{
    public function __construct(ChartOfAccount $model)
    {
        $this->model = $model;
    }

    public function account_list(Request $request)
    {
        if ($request->ajax()) {
            $warehouse_id = auth()->user()->warehouse->id;
            if($request->payment_method == 1)
            {
                $accounts = $this->model->where(['code' =>  $this->coa_head_code('cash_in_hand'),'status'=>1])->get();
            }elseif ($request->payment_method == 2) {
                $accounts = $this->model->with('bank')
                ->whereNotNull('bank_id')
                ->where('status',1)
                ->whereHas('bank',function($q) use ($warehouse_id){
                    $q->where('warehouse_id',$warehouse_id);
                })
                ->get();
            }elseif ($request->payment_method == 3) {
                $accounts = $this->model->with('mobile_bank')
                ->whereNotNull('mobile_bank_id')
                ->where('status',1)
                ->whereHas('mobile_bank',function($q) use ($warehouse_id){
                    $q->where('warehouse_id',$warehouse_id);
                })
                ->get();
            }

            $output = '';
            if ($accounts) {
                $output .= '<option value="">Select Please</option>';
                foreach ($accounts as $account) {
                    if($account->code != 1020102 && $account->code != 1020103){
                        $output .= "<option value='$account->id'>$account->name</option>";
                    }
                }
            }

            return $output;
        }
    }
}
