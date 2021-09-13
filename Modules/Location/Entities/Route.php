<?php

namespace Modules\Location\Entities;

use App\Models\BaseModel;

class Route extends BaseModel
{
    protected $table='locations';
    protected $fillable = ['name','parent_id','grand_parent_id','grand_grand_parent_id','type','status','created_by','modified_by'];

    public function district()
    {
        return $this->belongsTo(District::class,'grand_parent_id','id');
    }

    public function upazila()
    {
        return $this->belongsTo(Upazila::class,'parent_id','id');
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $routeName; 
    protected $parentID; 
    protected $grandParentID; 

    //methods to set custom search property value
    public function setName($routeName)
    {
        $this->routeName = $routeName;
    }
    public function setParentID($parentID)
    {
        $this->parentID = $parentID;
    }
    public function setGrandParentID($grandParentID)
    {
        $this->grandParentID = $grandParentID;
    }


    private function get_datatable_query()
    {
        $this->column_order = ['id','name','parent_id','status',null];
        
        $query = self::with('upazila')->where(['type'=>3,'grand_parent_id'=>auth()->user()->district_id]);

        //search query
        if (!empty($this->routeName)) {
            $query->where('name', 'like', '%' . $this->routeName . '%');
        }
        if (!empty($this->parentID)) {
            $query->where('parent_id', $this->parentID);
        }
        //order by data fetching code
        if (isset($this->orderValue) && isset($this->dirValue)) { //orderValue is the index number of table header and dirValue is asc or desc
            $query->orderBy($this->column_order[$this->orderValue], $this->dirValue); //fetch data order by matching column
        } else if (isset($this->order)) {
            $query->orderBy(key($this->order), $this->order[key($this->order)]);
        }
        return $query;
    }

    public function getDatatableList()
    {
        $query = $this->get_datatable_query();
        if ($this->lengthVlaue != -1) {
            $query->offset($this->startVlaue)->limit($this->lengthVlaue);
        }
        return $query->get();
    }

    public function count_filtered()
    {
        $query = $this->get_datatable_query();
        return $query->get()->count();
    }

    public function count_all()
    {
        return self::toBase()->where(['type'=>3])->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/


    public function upazila_id_wise_route_list(int $id)
    {
        return self::where('parent_id',$id)->pluck('name','id');
    }
}
