<?php

namespace Modules\Account\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\DebitVoucher;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Account\Http\Requests\DebitVoucherFormRequest;

class DebitVoucherController extends BaseController
{
    private const VOUCHER_PREFIX = 'DV';
    public function __construct(DebitVoucher $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('debit-voucher-access')){
            $this->setPageData('Debit Voucher List','Debit Voucher List','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Debit Voucher List']]);
            return view('account::debit-voucher.list');
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('debit-voucher-access')){

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
                    if(permission('debit-voucher-edit') && $value->approve != 1){
                        $action .= ' <a class="dropdown-item" href="'.route("debit.voucher.edit",$value->voucher_no).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('debit-voucher-view')){
                        $action .= ' <a class="dropdown-item view_data" data-id="' . $value->voucher_no . '" data-name="' . $value->voucher_no . '">'.self::ACTION_BUTTON['View'].'</a>';
                    }
                    if(permission('debit-voucher-delete') && $value->approve != 1){

                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->voucher_no . '" data-name="' . $value->voucher_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->voucher_no;
                    $row[] = date('d-M-Y',strtotime($value->voucher_date));;
                    $row[] = $value->description;
                    $row[] = number_format($value->debit,2);
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
        if(permission('debit-voucher-add')){
            $this->setPageData('Debit Voucher','Debit Voucher','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Debit Voucher']]);
            $data = [
            'voucher_no'             => self::VOUCHER_PREFIX.'-'.date('ymd').rand(1,999),
            'transactional_accounts' => ChartOfAccount::with('customer','bank','mobile_bank')->where(['status'=>1,'transaction'=>1])->whereNull('supplier_id')->orderBy('id','asc')->get(),
            'credit_accounts'        => ChartOfAccount::with('customer','bank','mobile_bank')->where(['status'=>1,'transaction'=>1])
                                    ->where('code','like','1020101')
                                    ->orWhere('code','like','10201020%')
                                    ->orWhere('code','like','10201030%')
                                    ->orderBy('id','asc')
                                    ->get(),
            'warehouse_id' => auth()->user()->warehouse->id,
            'district_id' => auth()->user()->district_id,
            ];
            return view('account::debit-voucher.create',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function store(DebitVoucherFormRequest $request)
    {
        if($request->ajax()){
            if(permission('debit-voucher-add')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $debit_voucher_transaction = [];
                    if ($request->has('debit_account')) {
                        $warehouse_id = auth()->user()->warehouse->id;
                        foreach ($request->debit_account as $key => $value) {
                            //Debit Insert
                            $debit_voucher_transaction[] = array(
                                'chart_of_account_id' => $value['id'],
                                'warehouse_id'        => $warehouse_id,
                                'voucher_no'          => $request->voucher_no,
                                'voucher_type'        => self::VOUCHER_PREFIX,
                                'voucher_date'        => $request->voucher_date,
                                'description'         => $request->remarks,
                                'debit'               => $value['amount'],
                                'credit'              => 0,
                                'posted'              => 1,
                                'approve'             => 3,
                                'created_by'          => auth()->user()->name,
                                'created_at'          => date('Y-m-d H:i:s')
                            );

                            //Cash In Insert
                            $credit_account = ChartOfAccount::find($request->credit_account_id);
                            $debit_voucher_transaction[] = array(
                                'chart_of_account_id' => $request->credit_account_id,
                                'warehouse_id'        => $warehouse_id,
                                'voucher_no'          => $request->voucher_no,
                                'voucher_type'        => self::VOUCHER_PREFIX,
                                'voucher_date'        => $request->voucher_date,
                                'description'         => 'Debit Voucher From '.($credit_account ? $credit_account->name : ''),
                                'debit'               => 0,
                                'credit'              => $value['amount'],
                                'posted'              => 1,
                                'approve'             => 3,
                                'created_by'          => auth()->user()->name,
                                'created_at'          => date('Y-m-d H:i:s')
                            );
                        }
                    }

                    // dd($debit_voucher_transaction);
                    $result = $this->model->insert($debit_voucher_transaction);
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

    public function show(Request $request)
    {
        if($request->ajax()){
            if(permission('debit-voucher-view')){
                $credit_voucher = DB::table('transactions as t')
                ->join('chart_of_accounts as coa','t.chart_of_account_id','=','coa.id')
                ->join('warehouses as w','t.warehouse_id','=','w.id')
                ->select('t.*','coa.name','w.name as warehouse_name')
                ->where(['voucher_no'=>$request->id,'debit'=>'0'])
                ->groupBy('t.chart_of_account_id')
                ->first();
                $debit_vouchers = DB::table('transactions as t')
                ->join('chart_of_accounts as coa','t.chart_of_account_id','=','coa.id')
                ->join('warehouses as w','t.warehouse_id','=','w.id')
                ->select('t.*','coa.name','w.name as warehouse_name')
                ->where(['voucher_no'=>$request->id,'credit'=>'0'])
                ->get();
                return view('account::debit-voucher.view-modal-data',compact('credit_voucher','debit_vouchers'))->render();
            }
        }
    }

    public function edit(string $voucher_no)
    {
        if(permission('debit-voucher-edit')){
            $voucher_data = $this->model->where('voucher_no',$voucher_no)->get();
            $data = [];
            if($voucher_data)
            {
                $data = [
                    'transactional_accounts' => ChartOfAccount::with('customer','bank','mobile_bank')->where(['status'=>1,'transaction'=>1])->whereNull('supplier_id')->orderBy('id','asc')->get(),
                    'credit_accounts'        => ChartOfAccount::with('customer','bank','mobile_bank')
                                            ->where(['status'=>1,'transaction'=>1])
                                            ->where('code','like','1020101')
                                            ->orWhere('code','like','10201020%')
                                            ->orWhere('code','like','10201030%')
                                            ->orderBy('id','asc')
                                            ->get(),
                    'warehouse_id' => auth()->user()->warehouse->id,
                    'district_id'  => auth()->user()->district_id,
                    'voucher'      => $voucher_data
                ];

                //Debit Voucher Update Info
                $data['debit_voucher'] = $this->model->where([['voucher_no',$voucher_no],['credit','<',1]])->get();

                // Debit Update Voucher Credit Info
                $data['credit_voucher'] = $this->model->where([['voucher_no',$voucher_no],['debit','<',1]])->get();

                $this->setPageData('Edit Debit Voucher','Edit Debit Voucher','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Edit Debit Voucher']]);

                return view('account::debit-voucher.edit',$data);
            }else{
                return redirect()->back();
            }
        }else{
            return $this->access_blocked();
        }
    }

    public function update(DebitVoucherFormRequest $request)
    {
        if($request->ajax()){
            if(permission('debit-voucher-edit')){
                // dd($request->all());
                DB::beginTransaction();
                try {
                    $this->model->where('voucher_no',$request->voucher_no)->delete();
                    $debit_voucher_transaction = [];
                    if ($request->has('debit_account')) {
                        $warehouse_id = auth()->user()->warehouse->id;
                        foreach ($request->debit_account as $key => $value) {
                            //Debit Insert
                            $debit_voucher_transaction[] = array(
                                'chart_of_account_id' => $value['id'],
                                'warehouse_id'        => $warehouse_id,
                                'voucher_no'          => $request->voucher_no,
                                'voucher_type'        => self::VOUCHER_PREFIX,
                                'voucher_date'        => $request->voucher_date,
                                'description'         => $request->remarks,
                                'debit'               => $value['amount'],
                                'credit'              => 0,
                                'posted'              => 1,
                                'approve'             => 3,
                                'created_by'          => auth()->user()->name,
                                'created_at'          => date('Y-m-d H:i:s')
                            );

                            //Cash In Insert
                            $credit_account = ChartOfAccount::find($request->credit_account_id);
                            $debit_voucher_transaction[] = array(
                                'chart_of_account_id' => $request->credit_account_id,
                                'warehouse_id'        => $warehouse_id,
                                'voucher_no'          => $request->voucher_no,
                                'voucher_type'        => self::VOUCHER_PREFIX,
                                'voucher_date'        => $request->voucher_date,
                                'description'         => 'Debit Voucher From '.($credit_account ? $credit_account->name : ''),
                                'debit'               => 0,
                                'credit'              => $value['amount'],
                                'posted'              => 1,
                                'approve'             => 3,
                                'created_by'          => auth()->user()->name,
                                'created_at'          => date('Y-m-d H:i:s')
                            );
                        }
                    }

                    // dd($debit_voucher_transaction);
                    $result = $this->model->insert($debit_voucher_transaction);
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
            if(permission('debit-voucher-delete')){
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
