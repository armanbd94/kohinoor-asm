<?php

namespace Modules\Product\Entities;

use App\Models\Tax;
use App\Models\Unit;
use App\Models\Category;
use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\Material\Entities\Material;
use Modules\Product\Entities\Attribute;
use Modules\Product\Entities\ProductVariant;
use Modules\Product\Entities\ProductAttribute;

class Product extends BaseModel
{
    protected $fillable = [ 'category_id', 'name', 'code',  'product_type', 'barcode_symbology', 
    'base_unit_id', 'unit_id', 'cost', 'base_unit_mrp', 'base_unit_price', 'unit_mrp', 'unit_price',
    'base_unit_qty', 'unit_qty', 'alert_quantity', 'image', 'tax_id', 'tax_method', 'status', 
    'description', 'created_by', 'modified_by'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class,'unit_id','id');
    }

    public function base_unit()
    {
        return $this->belongsTo(Unit::class,'base_unit_id','id');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class)->withDefault(['name'=>'No Tax','rate' => 0]);
    }

    public function product_material(){
        return $this->belongsToMany(Material::class,'product_material','product_id','material_id','id','id')
                    ->withTimestamps();
    }

    public function warehouse_product()
    {
        return $this->hasOne(WarehouseProduct::class,'product_id','id')
        ->select('product_id',DB::raw('sum(qty) as total_qty'),'warehouse_id')
        ->groupBy('product_id','warehouse_id');
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    protected $order = ['id' => 'asc'];
    //custom search column property
    protected $_name; 
    protected $_category_id; 
    protected $_product_type; 

    //methods to set custom search property value
    public function setName($name)
    {
        $this->_name = $name;
    }

    public function setCategoryID($category_id)
    {
        $this->_category_id = $category_id;
    }

    public function setProductType($product_type)
    {
        $this->_product_type = $product_type;
    }


    private function get_datatable_query()
    {

        $this->column_order = ['id', 'id','name', 'code','product_type', 'category_id',  'unit_qty', 'base_unit_qty', 'unit_mrp', 'unit_price','base_unit_mrp', 'base_unit_price',null,null];
        
        $query = self::with(['warehouse_product','category:id,name','unit','base_unit:id,unit_name,unit_code'])
        ->has('warehouse_product');

        //search query
        if (!empty($this->_name)) {
            $query->where('name', 'like', '%' . $this->_name . '%');
        }
        if (!empty($this->_category_id)) {
            $query->where('category_id', $this->_category_id);
        }
        if (!empty($this->_product_type)) {
            $query->where('product_type', $this->_product_type);
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
        return self::with(['warehouse_product'])
        ->has('warehouse_product')->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
    
}
