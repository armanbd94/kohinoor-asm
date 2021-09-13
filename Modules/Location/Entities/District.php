<?php

namespace Modules\Location\Entities;

use App\Models\BaseModel;


class District extends BaseModel
{
    protected $table='locations';
    protected $fillable = ['name','parent_id','grand_parent_id','grand_grand_parent_id','type','status','created_by','modified_by'];

    public function upazilas()
    {
        return $this->hasMany(Upazila::class,'parent_id','id');
    }
    public function routes()
    {
        return $this->hasMany(Route::class,'grand_parent_id','id');
    }
    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $districtName; 

    //methods to set custom search property value
    public function setName($districtName)
    {
        $this->districtName = $districtName;
    }


    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if (permission('district-bulk-delete')){
            $this->column_order = [null,'id','name','status',null];
        }else{
            $this->column_order = ['id','name','status',null];
        }
        
        $query = self::toBase()->where(['parent_id'=> 0,'type'=>1]);

        //search query
        if (!empty($this->districtName)) {
            $query->where('name', 'like', '%' . $this->districtName . '%');
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
        return self::toBase()->where(['parent_id'=> 0,'type'=>1])->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/


}
