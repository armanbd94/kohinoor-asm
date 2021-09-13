<?php
namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use Modules\Report\Entities\DueReport;

class DueReportController extends BaseController
{

    public function __construct(DueReport $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('due-report-access')){
            $this->setPageData('Due Report','Due Report','fab fa-opencart',[['name' => 'Due Report']]);
            $data = [
                'locations'   => DB::table('locations')->where('status', 1)->get(),
            ];
            return view('report::due-report',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if (!empty($request->memo_no)) {
                $this->model->setMemoNo($request->memo_no);
            }

            if (!empty($request->customer_id)) {
                $this->model->setCustomerID($request->customer_id);
            }

            if (!empty($request->start_date)) {
                $this->model->setStartDate($request->start_date);
            }
            if (!empty($request->end_date)) {
                $this->model->setEndDate($request->end_date);
            }

            if (!empty($request->area_id)) {
                $this->model->setAreaID($request->area_id);
            }
            if (!empty($request->upazila_id)) {
                $this->model->setUpazilaID($request->upazila_id);
            }
            if (!empty($request->route_id)) {
                $this->model->setRouteID($request->route_id);
            }
            if (!empty($request->payment_status)) {
                $this->model->setPaymentStatus($request->payment_status);
            }

            $this->set_datatable_default_properties($request);//set datatable default properties

            $list = $this->model->getDatatableList();//get table data
            $data = [];
            $no = $request->input('start');

            foreach ($list as $value) {
                $no++;
                $row = [];
                $row[] = $no;
                $row[] = $value->memo_no;
                $row[] = $value->shop_name.' - '.$value->name;
                $row[] = $value->upazila_name;
                $row[] = $value->route_name;
                $row[] = $value->area_name;
                $row[] = number_format($value->due_amount,2);
                $data[] = $row;
            }
            return [
                "draw" => $request->input('draw'),
                "recordsTotal" => $this->model->count_all(),
                "recordsFiltered" => $this->model->count_filtered(),
                "data" => $data,'total_due'=> $this->model->total_customer_dues($request->start_date,$request->end_date,$request->memo_no,$request->customer_id,$request->upazila_id,$request->route_id,$request->area_id)
            ];
            
            
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
