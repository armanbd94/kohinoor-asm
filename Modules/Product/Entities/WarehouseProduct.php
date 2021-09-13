<?php

namespace Modules\Product\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Setting\Entities\Warehouse;

class WarehouseProduct extends BaseModel
{
    protected $table = 'warehouse_product';
    protected $fillable = ['batch_no', 'warehouse_id', 'product_id', 'qty'];
    
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    protected $order = ['wp.product_id' => 'asc'];
    //custom search column property
    protected $_batch_no; 
    protected $_product_id; 

    //methods to set custom search property value
    public function setBatchNo($batch_no)
    {
        $this->_batch_no = $batch_no;
    }

    public function setProductID($product_id)
    {
        $this->_product_id = $product_id;
    }

    private function get_datatable_query()
    {

        $this->column_order = ['wp.id', 'wp.product_id', 'wp.batch_no','p.base_unit_id', 'p.base_unit_price', null,null];
        
        $query = DB::table('warehouse_product as wp')
        ->selectRaw('wp.*,p.name,p.base_unit_price,u.unit_name')
        ->join('products as p','wp.product_id','=','p.id')
        ->join('units as u','p.base_unit_id','=','u.id')
        ->where('wp.warehouse_id',auth()->user()->warehouse->id)
        ->groupBy('wp.batch_no','wp.product_id');

        //search query
        if (!empty($this->_batch_no)) {
            $query->where('wp.batch_no', $this->_batch_no);
        }
        if (!empty($this->_product_id)) {
            $query->where('wp.product_id', $this->_product_id);
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
        return DB::table('warehouse_product')
        ->where('warehouse_id',auth()->user()->warehouse->id)->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
