<?php

namespace Modules\Location\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Location\Entities\Upazila;
use Modules\Location\Entities\District;
use App\Http\Controllers\BaseController;
use Modules\Location\Http\Requests\LocationFormRequest;

class UpazilaController extends BaseController
{
    public function __construct(Upazila $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('upazila-access')){
            $this->setPageData('Upazila','Upazila','fas fa-map-marker-alt',[['name' => 'Location'],['name'=>'Upazila']]);
            return view('location::upazila.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if (!empty($request->upazila_name)) {
                $this->model->setName($request->upazila_name);
            }

            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();//get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $action = '';
                if(permission('upazila-edit')){
                $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['Edit'].'</a>';
                }
                if(permission('upazila-delete')){
                    $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                }

                $row = [];
                if(permission('upazila-bulk-delete')){
                    $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                }
                $row[] = $no;
                $row[] = $value->name;
                $row[] = permission('upazila-edit') ? change_status($value->id,$value->status, $value->name) : STATUS_LABEL[$value->status];
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
            if (permission('upazila-add') || permission('upazila-edit')){
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
            if(permission('upazila-edit')){
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
            if(permission('upazila-delete')){
                $upazila = $this->model->with('routes')->find($request->id);
                if (!$upazila->routes->isEmpty()) {
                    $output = ['status'=>'error','message'=>'This upazila can\'t delete beacuse it\'s related with upazilas data'];
                } else {
                    $result = $upazila->delete();
                    $output = $this->delete_message($result);
                }
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
            if(permission('upazila-bulk-delete')){
                $delete_list = [];
                $undelete_list = [];
                foreach ($request->ids as $id) {
                    $upazila = $this->model->with('upazilas','routes')->find($id);
                    if(!$upazila->routes->isEmpty()){
                        array_push($undelete_list,$upazila->name);
                    }else{
                        array_push($delete_list,$id);
                    }
                } 
                if(!empty($delete_list)){
                        $result = $upazila->destroy($delete_list);
                        $output = $result ?  ['status'=>'success','message'=> 'Selected Data Has Been Deleted Successfully. '. (!empty($undelete_list) ? 'Except these menus('.implode(',',$undelete_list).')'.' because they are associated with others data.' : '')] 
                        : ['status'=>'error','message'=>'Failed To Delete Data.'];
                }else{
                    $output = ['status'=>'error','message'=> !empty($undelete_list) ? 'These upazilas('.implode(',',$undelete_list).')'.' 
                    can\'t delete because they are associated with others data.' : ''];
                }
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
            if(permission('upazila-edit')){
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

    public function district_id_wise_upazila_list(int $id)
    {
        $upazilas = $this->model->district_id_wise_upazila_list($id);
        return json_encode($upazilas);
    }
}
