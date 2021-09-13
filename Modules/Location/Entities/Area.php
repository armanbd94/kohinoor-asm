<?php

namespace Modules\Location\Entities;

use App\Models\BaseModel;

class Area extends BaseModel
{
    protected $table='locations';
    protected $fillable = ['name','parent_id','grand_parent_id','grand_grand_parent_id','type','status','created_by','modified_by'];

    public function district()
    {
        return $this->belongsTo(District::class,'grand_grand_parent_id','id');
    }

    public function upazila()
    {
        return $this->belongsTo(District::class,'grand_parent_id','id');
    }

    public function route()
    {
        return $this->belongsTo(Upazila::class,'parent_id','id');
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $areaName; 
    protected $parentID; 
    protected $grandParentID; 
    protected $grandGrandParentID; 

    //methods to set custom search property value
    public function setName($areaName)
    {
        $this->areaName = $areaName;
    }
    public function setParentID($parentID)
    {
        $this->parentID = $parentID;
    }
    public function setGrandParentID($grandParentID)
    {
        $this->grandParentID = $grandParentID;
    }
    public function setGrandGrandParentID($grandGrandParentID)
    {
        $this->grandGrandParentID = $grandGrandParentID;
    }


    private function get_datatable_query()
    {
        $this->column_order = ['id','name','parent_id','grand_parent_id','status',null];
        
        $query = self::with('route','upazila')->where(['type'=>4,'grand_grand_parent_id'=>auth()->user()->district_id]);

        //search query
        if (!empty($this->areaName)) {
            $query->where('name', 'like', '%' . $this->areaName . '%');
        }
        if (!empty($this->parentID)) {
            $query->where('parent_id', $this->parentID);
        }
        if (!empty($this->grandParentID)) {
            $query->where('grand_parent_id', $this->grandParentID);
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
        return self::toBase()->where(['type'=>4])->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/


    public function route_id_wise_area_list(int $id)
    {
        return self::where('parent_id',$id)->pluck('name','id');
    }
}
