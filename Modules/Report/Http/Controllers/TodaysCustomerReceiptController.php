<?php
namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use Modules\Report\Entities\TodaysCustomerReceipt;

class TodaysCustomerReceiptController extends BaseController
{
    public function __construct(TodaysCustomerReceipt $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('todays-customer-receipt-access')){
            $this->setPageData('Today\'s Customer Receipt','Today\'s Customer Receipt','fab fa-opencart',[['name' => 'Today\'s Customer Receipt']]);
            $data = [
                'locations'   => DB::table('locations')->where('status', 1)->get(),
            ];
            return view('report::todays-customer-receipt',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if (!empty($request->customer_id)) {
                $this->model->setCustomerID($request->customer_id);
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
            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();//get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $row = [];
                $row[] = $no;
                $row[] = $value->shop_name.' - '.$value->customer_name;
                $row[] = $value->upazila_name;
                $row[] = $value->route_name;
                $row[] = $value->area_name;
                $row[] = $value->description;
                $row[] = number_format($value->credit,2);
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
            $this->model->count_filtered(), $data);
            
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
