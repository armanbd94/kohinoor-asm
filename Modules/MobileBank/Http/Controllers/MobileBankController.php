<?php

namespace Modules\MobileBank\Http\Controllers;


use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;
use Modules\MobileBank\Entities\MobileBank;
use Modules\Account\Entities\ChartOfAccount;
use Modules\MobileBank\Http\Requests\MobileBankFormRequest;

class MobileBankController extends BaseController
{
    private const CASH_AT_MOBILE_BANK = 'Cash At Mobile Bank';
    public function __construct(MobileBank $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('mobile-bank-access')){
            $this->setPageData('Mobile Bank List','Mobile Bank List','fas fa-mobile-alt',[['name' => 'Mobile Bank List']]);
            return view('mobilebank::index');
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('mobile-bank-access')){

                if (!empty($request->bank_name)) {
                    $this->model->setBankName($request->bank_name);
                }
                if (!empty($request->account_name)) {
                    $this->model->setAccountName($request->account_name);
                }
                if (!empty($request->account_number)) {
                    $this->model->setAccountNumber($request->account_number);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->bank_name;
                    $row[] = $value->account_name;
                    $row[] = $value->account_number;
                    $row[] = config('settings.currency_position') == '1' ? config('settings.currency_symbol').number_format($this->bank_balance($value->bank_name),2)
                                : number_format($this->bank_balance($value->bank_name),2).config('settings.currency_symbol');
                    $row[] = STATUS_LABEL[$value->status];
                    $data[] = $row;
                }
                return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
                $this->model->count_filtered(), $data);
            }
        }else{
            return response()->json($this->unauthorized());
        }
    }


    private function bank_balance(string $bank_name){
        $data = DB::table('transactions as t')
                ->leftjoin('chart_of_accounts as coa','t.chart_of_account_id','=','coa.id')
                ->select(DB::raw("SUM(t.debit) - SUM(t.credit) as balance"),'coa.name')
                ->groupBy('coa.name')
                ->where('coa.name',$bank_name)
                ->where('t.approve',1)
                ->first();
        return !empty($data) ? $data->balance : 0;
    }

    public function bank_ledger()
    {
        if(permission('mobile-bank-ledger-access')){
            $this->setPageData('Mobile Bank Ledger','Mobile Bank Ledger','fas fa-file-invoice-dollar',[['name' => 'Bank Ledger']]);
            $banks = MobileBank::where('warehouse_id',auth()->user()->warehouse->id)->get();
            return view('mobilebank::ledger',compact('banks'));
        }else{
            return $this->access_blocked();
        }
    }

    public function bank_ledger_data(Request $request)
    {
        if ($request->ajax()) {

            $from_date = !empty($request->from_date) ? $request->from_date : date('Y-m-d');
            $to_date   = !empty($request->to_date) ? $request->to_date : date('Y-m-d');

            $query = DB::table('transactions as t')
                ->select('t.id','t.chart_of_account_id', 't.voucher_no', 't.voucher_type', 't.voucher_date', 't.description', 't.debit', 't.credit', 't.posted', 't.approve','coa.name')
                ->leftjoin('chart_of_accounts as coa','t.chart_of_account_id','=','coa.id')
                ->where('coa.parent_name',self::CASH_AT_MOBILE_BANK);
            if(!empty($request->bank_name)){
                $query =  $query->where('coa.name',$request->bank_name);
            }
            
            $ledger_data = $query->whereDate('t.voucher_date','>=',$from_date)
                                ->whereDate('t.voucher_date','<=',$to_date)
                                ->where([['t.approve',1],['warehouse_id',auth()->user()->warehouse->id]])
                                ->orderBy('t.voucher_date','desc')
                                ->get();
            $ledger_data = $ledger_data->toArray();
            // dd($ledger_data);
            $total_credit = $total_debit = $balance = 0;

            if (!empty($ledger_data)) {
                foreach ($ledger_data as $index => $value) {
                        $ledger_data[$index]->debit_amount = $value->debit;
                        $total_debit += $ledger_data[$index]->debit_amount;
    
                        $ledger_data[$index]->balance = $balance + ($value->debit - $value->credit);
                        $ledger_data[$index]->credit_amount  = $value->credit;
                        $total_credit += $ledger_data[$index]->credit_amount;
                        $balance = $ledger_data[$index]->balance;
                  
                }
            }

            $data = [
                'ledger'       => $ledger_data,
                'total_debit'  => number_format($total_debit,2),
                'total_credit' => number_format($total_credit,2),
                'balance'      => number_format($balance,2)
            ];

            return view('mobilebank::ledger-data',$data)->render();
        }
    }

}
