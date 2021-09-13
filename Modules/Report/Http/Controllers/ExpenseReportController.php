<?php

namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Modules\Expense\Entities\ExpenseItem;
use Modules\Report\Entities\ExpenseReport;

class ExpenseReportController extends BaseController
{
    public function __construct(ExpenseReport $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('product-wise-sales-report-access')){
            $this->setPageData('Expense Report','Expense Report','fas fa-file-signature',[['name' => 'Report'],['name' => 'Expense Report']]);
            $expense_items = ExpenseItem::toBase()->get();
            return view('report::expense-report',compact('expense_items'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if (!empty($request->expense_item_id)) {
                $this->model->setExpenseItemID($request->expense_item_id);
            }
            if (!empty($request->start_date)) {
                $this->model->setStartDate($request->start_date);
            }
            if (!empty($request->end_date)) {
                $this->model->setEndDate($request->end_date);
            }
            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();//get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $row = [];
                $row[] = $no;
                $row[] = $value->voucher_no;
                $row[] = date(config('settings.date_format',strtotime($value->date)));
                $row[] = $value->expense_name;
                $row[] = $value->remarks;
                $row[] = PAYMENT_METHOD[$value->payment_type];
                $row[] = $value->account_name;
                $row[] = number_format($value->amount,2,'.','');
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
            $this->model->count_filtered(), $data);
            
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
