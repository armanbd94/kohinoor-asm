<?php

namespace Modules\Location\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Location\Entities\Route;
use Modules\Location\Entities\Upazila;
use Modules\Location\Entities\District;
use App\Http\Controllers\BaseController;
use Modules\Location\Http\Requests\LocationFormRequest;

class RouteController extends BaseController
{
    public function __construct(Route $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('route-access')){
            $this->setPageData('Route','Route','fas fa-map-marker-alt',[['name' => 'Location'],['name'=>'Route']]);
            $upazilas  = Upazila::where(['type' => 2,'status' => 1,'parent_id' => auth()->user()->district_id])->pluck('name','id');
            return view('location::upazila-route.index',compact('upazilas'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if (!empty($request->route_name)) {
                $this->model->setName($request->route_name);
            }
            if (!empty($request->parent_id)) {
                $this->model->setParentID($request->parent_id);
            }

            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();//get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $action = '';
                if(permission('route-edit')){
                $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['Edit'].'</a>';
                }
                if(permission('route-delete')){
                    $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                }

                $row = [];
                if(permission('route-bulk-delete')){
                    $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                }
                $row[] = $no;
                $row[] = $value->name;
                $row[] = $value->upazila->name;
                $row[] = permission('route-edit') ? change_status($value->id,$value->status, $value->name) : STATUS_LABEL[$value->status];
                $row[] = action_button($action);//custom helper function for action button
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
             $this->model->count_filtered(), $data);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function store_or_update_data(LocationFormRequest $request)
    {
        if($request->ajax()){
            if (permission('route-add') || permission('route-edit')){
                $collection   = collect($request->validated());
                $collection   = $this->track_data($collection,$request->update_id);
                $result       = $this->model->updateOrCreate(['id'=>$request->update_id],$collection->all());
                $output       = $this->store_message($result, $request->update_id);
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function edit(Request $request)
    {
        if($request->ajax()){
            if(permission('route-edit')){
                $data   = $this->model->findOrFail($request->id);
                $output = $this->data_message($data); //if data found then it will return data otherwise return error message
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
            if(permission('route-delete')){
                $route = $this->model->find($request->id);
                $result = $route->delete();
                $output = $this->delete_message($result);
            }else{
                $output = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function bulk_delete(Request $request)
    {
        if($request->ajax()){
            if(permission('route-bulk-delete')){
                $result = $this->model->destroy($request->ids);
                $output = $this->bulk_delete_message($result);
                // $delete_list = [];
                // $undelete_list = [];
                // foreach ($request->ids as $id) {
                //     $upazila = $this->model->with('upazilas','routes')->find($id);
                //     if(!$route->routes->isEmpty()){
                //         array_push($undelete_list,$route->name);
                //     }else{
                //         array_push($delete_list,$id);
                //     }
                // } 
                // if(!empty($delete_list)){
                //         $result = $route->destroy($delete_list);
                //         $output = $result ?  ['status'=>'success','message'=> 'Selected Data Has Been Deleted Successfully. '. (!empty($undelete_list) ? 'Except these menus('.implode(',',$undelete_list).')'.' because they are associated with others data.' : '')] 
                //         : ['status'=>'error','message'=>'Failed To Delete Data.'];
                // }else{
                //     $output = ['status'=>'error','message'=> !empty($undelete_list) ? 'These upazilas('.implode(',',$undelete_list).')'.' 
                //     can\'t delete because they are associated with others data.' : ''];
                // }
            }else{
                $output = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function change_status(Request $request)
    {
        if($request->ajax()){
            if(permission('route-edit')){
                $result   = $this->model->find($request->id)->update(['status' => $request->status]);
                $output   = $result ? ['status' => 'success','message' => 'Status Has Been Changed Successfully']
                : ['status' => 'error','message' => 'Failed To Change Status'];
            }else{
                $output   = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function upazila_id_wise_route_list(int $id)
    {
        $routes = $this->model->upazila_id_wise_route_list($id);
        return json_encode($routes);
    }
}
