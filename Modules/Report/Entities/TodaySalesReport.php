<?php
namespace Modules\Report\Entities;

use App\Models\BaseModel;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Customer\Entities\Customer;
use Modules\Location\Entities\Area;
use Modules\Location\Entities\District;
use Modules\Location\Entities\Route;
use Modules\Location\Entities\Upazila;
use Modules\SalesMen\Entities\Salesmen;


class TodaySalesReport extends BaseModel
{
    protected $table = 'sales';
    protected $fillable = ['memo_no', 'warehouse_id', 'district_id', 'upazila_id', 'route_id', 'area_id', 
    'salesmen_id', 'customer_id', 'item', 'total_qty', 'total_discount', 'total_tax', 'total_price', 
    'order_tax_rate', 'order_tax', 'order_discount', 'shipping_cost', 'labor_cost', 'grand_total', 
    'previous_due', 'net_total', 'paid_amount', 'due_amount', 'payment_status', 'payment_method', 
    'account_id', 'reference_no', 'document', 'note', 'sale_date', 'created_by', 'modified_by' ];


    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class,'warehouse_id','id');
    }
    public function district()
    {
        return $this->belongsTo(District::class,'district_id','id');
    }
    public function upazila()
    {
        return $this->belongsTo(Upazila::class,'upazila_id','id');
    }
    public function route()
    {
        return $this->belongsTo(Route::class,'route_id','id');
    }
    public function area()
    {
        return $this->belongsTo(Area::class,'area_id','id');
    }
    public function salesmen()
    {
        return $this->belongsTo(Salesmen::class,'salesmen_id','id');
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class,'customer_id','id');
    }

    public function sale_products()
    {
        return $this->belongsToMany(Product::class,'sale_products','sale_id','product_id','id','id')
        ->withPivot('id', 'batch_no','qty', 'sale_unit_id', 'net_unit_price', 'discount', 'tax_rate', 'tax', 'total')
        ->withTimestamps(); 
    }


     /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $_memo_no; 
    protected $_warehouse_id; 
    protected $_district_id; 
    protected $_upazila_id; 
    protected $_route_id; 
    protected $_area_id; 
    protected $_salesmen_id; 
    protected $_customer_id; 
    protected $_payment_status; 

    //methods to set custom search property value
    public function setMemoNo($memo_no)
    {
        $this->_memo_no = $memo_no;
    }

    public function setWarehouseID($warehouse_id)
    {
        $this->_warehouse_id = $warehouse_id;
    }

    public function setDistrictID($district_id)
    {
        $this->_district_id = $district_id;
    }

    public function setUpazilaID($upazila_id)
    {
        $this->_upazila_id = $upazila_id;
    }
    public function setRouteID($route_id)
    {
        $this->_route_id = $route_id;
    }
    public function setAreaID($area_id)
    {
        $this->_area_id = $area_id;
    }

    public function setSalesmenID($salesmen_id)
    {
        $this->_salesmen_id = $salesmen_id;
    }
    public function setCustomerID($customer_id)
    {
        $this->_customer_id = $customer_id;
    }

    public function setPaymentStatus($payment_status)
    {
        $this->_payment_status = $payment_status;
    }



    private function get_datatable_query()
    {
        $this->column_order = ['s.id','s.memo_no', 's.salesmen_id','s.upazila_id','s.route_id','s.area_id','s.custoemr_id', 's.grand_total'];

        $query = DB::table('sales as s')
        ->selectRaw('s.*,sm.name as salesman_name,sm.phone,c.name,c.shop_name,w.name as warehouse_name,
        d.name as district_name,u.name as upazila_name,r.name as route_name,a.name as area_name')
        ->join('salesmen as sm','s.salesmen_id','=','sm.id')
        ->join('customers as c','s.customer_id','=','c.id')
        ->join('warehouses as w','s.warehouse_id','=','w.id')
        ->join('locations as d', 'c.district_id', '=', 'd.id')
        ->join('locations as u', 'c.upazila_id', '=', 'u.id')
        ->join('locations as r', 'c.route_id', '=', 'r.id')
        ->join('locations as a', 'c.area_id', '=', 'a.id')
        ->where([['s.warehouse_id',auth()->user()->warehouse->id],['s.sale_date',date('Y-m-d')]]);

        //search query
        if (!empty($this->_memo_no)) {
            $query->where('s.memo_no', $this->_memo_no);
        }

        if (!empty($this->_warehouse_id)) {
            $query->where('s.warehouse_id', $this->_warehouse_id);
        }
        if (!empty($this->_salesmen_id)) {
            $query->where('s.salesmen_id', $this->_salesmen_id);
        }
       
        if (!empty($this->_customer_id)) {
            $query->where('s.customer_id', $this->_customer_id);
        }
        
        if (!empty($this->_district_id)) {
            $query->where('c.district_id', $this->_district_id);
        }
        if (!empty($this->_upazila_id)) {
            $query->where('c.upazila_id', $this->_upazila_id);
        }
        if (!empty($this->_route_id)) {
            $query->where('c.route_id', $this->_route_id);
        }
        if (!empty($this->_area_id)) {
            $query->where('c.area_id', $this->_area_id);
        }

        if (!empty($this->_payment_status)) {
            $query->where('payment_status', $this->_payment_status);
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
        return DB::table('sales')->where([['warehouse_id',auth()->user()->warehouse->id],['sale_date',date('Y-m-d')]])->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/


}
