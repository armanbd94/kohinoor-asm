<?php

namespace App\Models;

use App\Models\ASM;
use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Modules\Product\Entities\Product;
use Modules\Location\Entities\District;
use Modules\Product\Entities\WarehouseProduct;

class Warehouse extends BaseModel
{
    protected $fillable = ['name', 'phone', 'email', 'address','district_id','asm_id', 'status', 'deletable', 'created_by', 'modified_by'];


    public function warehouse_products()
    {
        return $this->hasMany(WarehouseProduct::class,'warehouse-id','id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class,'warehouse_product','warehouse_id','product_id','id','id')
        ->withPivot('id','batch_no','qty')
        ->withTimestamps();
    }



    public function district()
    {
        return $this->belongsTo(District::class,'district_id','id');
    }

    public function asm()
    {
        return $this->belongsTo(ASM::class,'asm_id','id');
    }
    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $order = ['w.id' => 'desc'];
    protected $name; 

    //methods to set custom search property value
    public function setName($name)
    {
        $this->name = $name;
    }


    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)
        if (permission('warehouse-bulk-delete')){
            $this->column_order = [null,'w.id','w.name','w.district_id','w.asm_id', 'w.phone','w.email','w.address','w.status',null];
        }else{
            $this->column_order = ['w.id','w.name','w.district_id','w.asm_id', 'w.phone','w.email','w.address','w.status',null];
        }
        
        $query = DB::table('warehouses as w')
                ->select('w.id','w.name', 'w.phone', 'w.email', 'w.address','w.district_id','w.asm_id', 
                'w.status', 'w.deletable','d.name as district_name','a.name as asm_name','a.phone as asm_phone')
                ->join('locations as d','w.district_id','=','d.id')
                ->join('asms as a','w.asm_id','=','a.id');

        //search query
        if (!empty($this->name)) {
            $query->where('w.name', 'like', '%' . $this->name . '%');
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
        return self::toBase()->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/

    /*************************************
    * * *  Begin :: Cache Data * * *
    **************************************/
    protected const ALL_WAREHOUSES    = '_warehouses';
    protected const ACTIVE_WAREHOUSES    = '_active_warehouses';

    public static function allWarehouses(){
        return Cache::rememberForever(self::ALL_WAREHOUSES, function () {
            return self::toBase()->get();
        });
    }
    public static function activeWarehouses(){
        return Cache::rememberForever(self::ACTIVE_WAREHOUSES, function () {
            return self::toBase()->where('status',1)->get();
        });
    }


    public static function flushCache(){
        Cache::forget(self::ALL_WAREHOUSES);
        Cache::forget(self::ACTIVE_WAREHOUSES);
    }


    public static function boot(){
        parent::boot();

        static::updated(function () {
            self::flushCache();
        });

        static::created(function() {
            self::flushCache();
        });

        static::deleted(function() {
            self::flushCache();
        });
    }
    /***********************************
    * * *  Begin :: Cache Data * * *
    ************************************/
}
