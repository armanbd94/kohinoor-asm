<?php
namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\SalesMen\Entities\Salesmen;
use App\Http\Controllers\BaseController;
use Modules\Report\Entities\SalesmenWiseSalesReport;

class SalesmanWiseSalesReportController extends BaseController
{
    public function __construct(SalesmenWiseSalesReport $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('salesman-wise-sales-report-access')){
            $this->setPageData('Salesman Wise Sales Report','Salesman Wise Sales Report','fas fa-file-signature',[['name' => 'Report'],['name' => 'Salesman Wise Sales Report']]);
            $salesmen = Salesmen::with('district','upazila')->where(['status'=>1])->get();
            return view('report::salesman-wise-sales-report',compact('salesmen'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if (!empty($request->salesmen_id)) {
                $this->model->setSalesmanID($request->salesmen_id);
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
                $row[] = $value->name.' ( '.$value->phone.')';
                $row[] = $value->total_item.'('.$value->total_qty.')';
                $row[] = number_format($value->total_amount,2);
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
            $this->model->count_filtered(), $data);
            
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
