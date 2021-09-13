<?php

namespace Modules\Account\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;
use Modules\Account\Entities\ContraVoucher;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Account\Http\Requests\ContraVoucherFormRequest;

class ContraVoucherController extends BaseController
{
    private const VOUCHER_PREFIX = 'CONTRAV';
    public function __construct(ContraVoucher $model)
    {
        $this->model = $model;
    }


    public function index()
    {
        if(permission('contra-voucher-access')){
            $this->setPageData('Contra Voucher List','Contra Voucher List','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Contra Voucher List']]);
            return view('account::contra-voucher.list');
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('contra-voucher-access')){

                if (!empty($request->start_date)) {
                    $this->model->setStartDate($request->start_date);
                }
                if (!empty($request->end_date)) {
                    $this->model->setEndDate($request->end_date);
                }
                if (!empty($request->voucher_no)) {
                    $this->model->setVoucherNo($request->voucher_no);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('contra-voucher-edit') && $value->approve != 1){
                        $action .= ' <a class="dropdown-item" href="'.route("contra.voucher.edit",$value->voucher_no).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('contra-voucher-view')){
                        $action .= ' <a class="dropdown-item view_data" data-id="' . $value->voucher_no . '" data-name="' . $value->voucher_no . '">'.self::ACTION_BUTTON['View'].'</a>';
                    }
                    if(permission('contra-voucher-delete') && $value->approve != 1){

                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->voucher_no . '" data-name="' . $value->voucher_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->voucher_no;
                    $row[] = date('d-M-Y',strtotime($value->voucher_date));;
                    $row[] = $value->description;
                    $row[] = number_format($value->debit,2);
                    $row[] = number_format($value->credit,2);
                    $row[] = VOUCHER_APPROVE_STATUS_LABEL[$value->approve];
                    $row[] = $value->created_by;
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
        if(permission('contra-voucher-add')){
            $this->setPageData('Contra Voucher','Contra Voucher','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Contra Voucher']]);
            $data = [
                'voucher_no'             => self::VOUCHER_PREFIX.'-'.date('ymdHis').rand(1,99),
                'transactional_accounts' => ChartOfAccount::with('customer','bank','mobile_bank')
                ->where(['status'=>1,'transaction'=>1])
                ->whereNull('supplier_id')
                ->orderBy('id','asc')->get(),
                'warehouse_id' => auth()->user()->warehouse->id,
                'district_id' => auth()->user()->district_id,
            ];
            return view('account::contra-voucher.create',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function store(ContraVoucherFormRequest $request)
    {
        if($request->ajax()){
            if(permission('contra-voucher-add')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $contra_voucher_transaction = [];
                    if ($request->has('contra_account')) {
                        $warehouse_id = auth()->user()->warehouse->id;
                        foreach ($request->contra_account as $key => $value) {
                            if(!empty($value['debit_amount']) || !empty($value['credit_amount'] ))
                            {
                                $contra_voucher_transaction[] = array(
                                    'chart_of_account_id' => $value['id'],
                                    'warehouse_id'        => $warehouse_id,
                                    'voucher_no'          => $request->voucher_no,
                                    'voucher_type'        => self::VOUCHER_PREFIX,
                                    'voucher_date'        => $request->voucher_date,
                                    'description'         => $request->remarks,
                                    'debit'               => $value['debit_amount'] ? $value['debit_amount'] : 0,
                                    'credit'              => $value['credit_amount'] ? $value['credit_amount'] : 0,
                                    'posted'              => 1,
                                    'approve'             => 3,
                                    'created_by'          => auth()->user()->name,
                                    'created_at'          => date('Y-m-d H:i:s')
                                );
                            }
                        }
                    }

                    $result = $this->model->insert($contra_voucher_transaction);
                    $output = $this->store_message($result, null);
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
            }else{
                $output = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function edit(string $voucher_no)
    {
        if(permission('contra-voucher-edit')){
            $voucher_data = $this->model->where('voucher_no',$voucher_no)->get();
            $data = [];
            if($voucher_data)
            {
                $data = [
                    'transactional_accounts' => ChartOfAccount::with('customer','bank','mobile_bank')
                                                    ->where(['status'=>1,'transaction'=>1])
                                                    ->whereNull('supplier_id')
                                                    ->orderBy('id','asc')
                                                    ->get(),

                    'warehouse_id' => auth()->user()->warehouse->id,
                    'district_id'  => auth()->user()->district_id,
                    'voucher'      => $voucher_data
                ];

                $this->setPageData('Edit Contra Voucher','Edit Contra Voucher','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Edit Contra Voucher']]);

                return view('account::contra-voucher.edit',$data);
            }else{
                return redirect()->back();
            }
        }else{
            return $this->access_blocked();
        }
    }

    public function show(Request $request)
    {
        if($request->ajax()){
            if(permission('contra-voucher-view')){
                $voucher = $this->model->with('coa','warehouse')->where('voucher_no',$request->id)->get();
                return view('account::contra-voucher.view-modal-data',compact('voucher'))->render();
            }
        }
    }

    public function update(ContraVoucherFormRequest $request)
    {
        if($request->ajax()){
            if(permission('contra-voucher-edit')){
                DB::beginTransaction();
                try {
                    Transaction::where('voucher_no',$request->voucher_no)->delete();
                    $contra_voucher_transaction = [];
                    if ($request->has('contra_account')) {
                        $warehouse_id = auth()->user()->warehouse->id;
                        foreach ($request->contra_account as $key => $value) {
                            //Credit Insert
                            if(!empty($value['debit_amount']) || !empty($value['credit_amount'] ))
                            {
                                $contra_voucher_transaction[] = array(
                                    'chart_of_account_id' => $value['id'],
                                    'warehouse_id'        => $warehouse_id,
                                    'voucher_no'          => $request->voucher_no,
                                    'voucher_type'        => self::VOUCHER_PREFIX,
                                    'voucher_date'        => $request->voucher_date,
                                    'description'         => $request->remarks,
                                    'debit'               => $value['debit_amount'] ? $value['debit_amount'] : 0,
                                    'credit'              => $value['credit_amount'] ? $value['credit_amount'] : 0,
                                    'posted'              => 1,
                                    'approve'             => 3,
                                    'created_by'          => auth()->user()->name,
                                    'created_at'          => date('Y-m-d H:i:s')
                                );
                            }
                        }
                    }

                    $result = $this->model->insert($contra_voucher_transaction);
                    $output = $this->store_message($result, null);
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
            }else{
                $output = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function delete(Request $request)
    {
        if($request->ajax()){
            if(permission('contra-voucher-delete')){
                $result  = $this->model->where([['voucher_no',$request->id],['warehouse_id',auth()->user()->warehouse->id]])->delete();
                $output   = $this->delete_message($result);
                return response()->json($output);
            }else{
                return response()->json($this->unauthorized());
            }
        }else{
            return response()->json($this->unauthorized());
        }
    } 

}
