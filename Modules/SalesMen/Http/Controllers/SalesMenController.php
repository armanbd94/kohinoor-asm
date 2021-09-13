<?php

namespace Modules\SalesMen\Http\Controllers;

use Exception;
use App\Models\Warehouse;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Location\Entities\Route;
use Modules\SalesMen\Entities\Salesmen;
use App\Http\Controllers\BaseController;
use Modules\SalesMen\Entities\SalesMenDailyRoute;
use Modules\SalesMen\Http\Requests\SalesMenFormRequest;

class SalesMenController extends BaseController
{
    use UploadAble;
    public function __construct(Salesmen $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('sr-access')){
            $this->setPageData('Sales Representative','Sales Representative','fas fa-user-secret',[['name' => 'Sales Representative']]);
            $locations = DB::table('locations')->select('id','name','type')->where([['type','<>',4],['status',1]])->get();
            return view('salesmen::index',compact('locations'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if (!empty($request->name)) {
                $this->model->setName($request->name);
            }
            if (!empty($request->username)) {
                $this->model->setUsername($request->username);
            }
            if (!empty($request->phone)) {
                $this->model->setPhone($request->phone);
            }
            if (!empty($request->email)) {
                $this->model->setEmail($request->email);
            }
            if (!empty($request->upazila_id)) {
                $this->model->setUpazilaID($request->upazila_id);
            }
            if (!empty($request->status)) {
                $this->model->setStatus($request->status);
            }

            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();//get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $action = '';
                if(permission('sr-edit')){
                $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['Edit'].'</a>';
                }
                if(permission('sr-view')){
                $action .= ' <a class="dropdown-item view_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['View'].'</a>';
                }
                if(permission('sr-delete')){
                    $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                }


                $row = [];
                if(permission('sr-bulk-delete')){
                    $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                }
                $row[] = $no;
                $row[] = $this->table_image(SALESMEN_AVATAR_PATH,$value->avatar,$value->name,1);
                $row[] = $value->name;
                $row[] = $value->username;
                $row[] = number_format($value->monthly_target_value,2,'.','');
                $row[] = $value->phone;
                $row[] = $value->upazila->name;
                $row[] = $value->email ? $value->email : '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">No Email</span>';
                $row[] = permission('sr-edit') ? change_status($value->id,$value->status, $value->name) : STATUS_LABEL[$value->status];
                $row[] = action_button($action);//custom helper function for action button
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
             $this->model->count_filtered(), $data);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function show(Request $request)
    {
        if($request->ajax()){
            if(permission('sr-view')){
                $salesmen = $this->model->with('upazila','routes')->findOrFail($request->id);
                // dd($salesmen->routes[0]);
                return view('salesmen::view-data',compact('salesmen'))->render();
            }
        }
    }

    public function daily_route_list(Request $request)
    {
        if ($request->ajax()) {
            $routes = SalesMenDailyRoute::with('route')->where('salesmen_id',$request->id)->get();
            $output = '';
            if($routes)
            {
                $output .= '<option value="">Select Please</option>';
                foreach ($routes as $key => $value) {
                    $output .= '<option value="'.$value->route_id.'">'.DAYS[$value->day].' - '.$value->route->name.'</option>';
                }
            }

            return $output;
        }
    }

}
