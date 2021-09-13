<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

class ReceivedCoupon extends BaseModel
{
    protected $fillable = ['salesmen_id', 'customer_id', 'coupon_id'];

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    protected $order = ['rc.id' => 'asc'];
    //custom search column property
    protected $_batch_no; 
    protected $_product_id; 
    protected $_district_id; 
    protected $_upazila_id; 
    protected $_route_id; 
    protected $_area_id; 
    protected $_salesmen_id; 
    protected $_warehouse_id; 
    protected $_customer_id; 
    protected $_start_date; 
    protected $_end_date; 

    //methods to set custom search property value
    public function setBatchNo($batch_no)
    {
        $this->_batch_no = $batch_no;
    }
    public function setProductID($product_id)
    {
        $this->_product_id = $product_id;
    }

    public function setSalesmenID($salesmen_id)
    {
        $this->_salesmen_id = $salesmen_id;
    }

    public function setWarehouseID($warehouse_id)
    {
        $this->_warehouse_id = $warehouse_id;
    }

    public function setCustomerID($customer_id)
    {
        $this->_customer_id = $customer_id;
    }

    public function setStartDate($start_date)
    {
        $this->_start_date = $start_date;
    }

    public function setEndDate($end_date)
    {
        $this->_end_date = $end_date;
    }

    public function setDistrictID($district_id)
    {
        $this->_district_id = $district_id;
    }
    public function setUpazilaID($upazila_id)
    {
        $this->_upazila_id = $upazila_id;
    }
    public function seRouteID($route_id)
    {
        $this->_route_id = $route_id;
    }

    public function setAreaID($area_id)
    {
        $this->_area_id = $area_id;
    }

    private function get_datatable_query()
    {

        $this->column_order = ['rc.id', 'pc.batch_no', 'pc.coupon_code','pc.status','pp.product_id', 'c.name',  's.name', 
        'c.upazila_id', 'c.route_id', 'c.area_id','rc.created_at', 'pp.coupon_price'];
        
        $query = DB::table('received_coupons as rc')
        ->join('production_coupons as pc','rc.coupon_id','=','pc.id')
        ->join('production_products as pp','pc.production_product_id','=','pp.id')
        ->join('products as p','pp.product_id','=','p.id')
        ->join('salesmen as s','rc.salesmen_id','=','s.id')
        ->join('warehouses as w','s.warehouse_id','=','w.id')
        ->join('customers as c','rc.customer_id','=','c.id')
        ->join('locations as d','c.district_id','=','d.id')
        ->join('locations as u','c.upazila_id','=','u.id')
        ->join('locations as r','c.route_id','=','r.id')
        ->join('locations as a','c.area_id','=','a.id')
        ->where([['s.warehouse_id',auth()->user()->warehouse->id],['pc.status',1]])
        ->selectRaw('rc.*,pc.batch_no,pc.coupon_code,pc.status,pp.coupon_price,p.name as product_name,
        s.name as received_by,c.name as received_from,w.name as warehouse_name,d.name as district_name,
        u.name as upazila_name,r.name as route_name,a.name as area_name');

        //search query
        if (!empty($this->_batch_no)) {
            $query->where('pc.batch_no', $this->_batch_no);
        }
        if (!empty($this->_product_id)) {
            $query->where('p.id', $this->_product_id);
        }
        if (!empty($this->_salesmen_id)) {
            $query->where('rc.salesmen_id', $this->_salesmen_id);
        }
        if (!empty($this->_warehouse_id)) {
            $query->where('s.warehouse_id', $this->_warehouse_id);
        }
        if (!empty($this->_customer_id)) {
            $query->where('rc.customer_id', $this->_customer_id);
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
        if (!empty($this->_start_date) && !empty($this->_end_date)) {
            $query->whereDate('rc.created_at', '>=',$this->_start_date)->whereDate('rc.created_at', '<=',$this->_end_date);
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
        return DB::table('received_coupons as rc')
        ->join('salesmen as s','rc.salesmen_id','=','s.id')
        ->where([['s.warehouse_id',auth()->user()->warehouse->id]])
        ->selectRaw('rc.*')->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
