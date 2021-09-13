<?php

namespace Modules\Report\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

class DueReport extends BaseModel
{
    
    protected $table = 'sales';

    protected $fillable = [ 'memo_no', 'warehouse_id', 'district_id', 'upazila_id', 'route_id', 'area_id', 
    'salesmen_id', 'customer_id', 'item', 'total_qty', 'total_discount', 'total_tax', 'total_price', 
    'order_tax_rate', 'order_tax', 'order_discount', 'shipping_cost', 'labor_cost', 'grand_total', 
    'previous_due', 'net_total', 'paid_amount', 'due_amount', 'payment_status', 'payment_method', 
    'account_id', 'reference_no', 'document', 'note', 'sale_date', 'created_by', 'modified_by'
    ];


    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $order = ['s.sale_date' => 'desc'];
    protected $start_date; 
    protected $end_date; 
    protected $_memo_no; 
    protected $_warehouse_id; 
    protected $_district_id; 
    protected $_upazila_id; 
    protected $_route_id; 
    protected $_area_id; 
    protected $_customer_id; 

    //methods to set custom search property value

    public function setStartDate($start_date)
    {
        $this->start_date = $start_date;
    }
    public function setEndDate($end_date)
    {
        $this->end_date = $end_date;
    }

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

    public function setCustomerID($customer_id)
    {
        $this->_customer_id = $customer_id;
    }

    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)

        $this->column_order = ['s,id','s.sale_date', 's.memo_no','s.customer_id','s.upazila_id','s.route_id','s.area_id','s.due_amount'];
        
        
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
        ->where([['s.warehouse_id',auth()->user()->warehouse->id],['s.due_amount','>',0]]);
        //search query
        if (!empty($this->start_date)) {
            $query->where('s.sale_date', '>=',$this->start_date);
        }
        if (!empty($this->end_date)) {
            $query->where('s.sale_date', '<=',$this->end_date);
        }

        if (!empty($this->_memo_no)) {
            $query->where('s.memo_no', $this->_memo_no);
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
        return DB::table('sales')
        ->where([['warehouse_id',auth()->user()->warehouse->id],['due_amount','>',0]])
        ->get()->count();
    }

    public function total_customer_dues($start_date=null,$end_date=null,$memo_no=null,$customer_id=null,$upazila_id=null,$route_id=null,$area_id=null)
    {
       $customer_dues = DB::table('sales as s')
       ->leftJoin('customers as c','s.customer_id','=','c.id')
            ->selectRaw('s.customer_id,s.due_amount,max(s.id) as last_due_id')
            ->groupBy('s.customer_id')
            ->where([['s.warehouse_id',auth()->user()->warehouse->id],['s.due_amount','>',0]])
            ->when($start_date,function($q) use($start_date){
                $q->whereDate('s.sale_date','>=',$start_date);
            })
            ->when($end_date,function($q) use($end_date){
                $q->whereDate('s.sale_date','<=',$end_date);
            })
            ->when($memo_no,function($q) use($memo_no){
                $q->where('s.memo_no',$memo_no);
            })
            ->when($customer_id,function($q) use($customer_id){
                $q->where('s.customer_id',$customer_id);
            })
            ->when($upazila_id,function($q) use($upazila_id){
                $q->where('c.upazila_id',$upazila_id);
            })
            ->when($route_id,function($q) use($route_id){
                $q->where('c.route_id',$route_id);
            })
            ->when($area_id,function($q) use($area_id){
                $q->where('c.area_id',$area_id);
            })
            ->get();
        $total_dues = 0;
        if($customer_dues)
        {
            foreach ($customer_dues->chunk(10) as $chunk) {
                foreach ($chunk as $value)
                {
                    $total_dues += $value->due_amount;
                }
            }
        }
        return $total_dues;
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
