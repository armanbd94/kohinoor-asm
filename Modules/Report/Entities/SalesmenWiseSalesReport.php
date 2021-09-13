<?php

namespace Modules\Report\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

class SalesmenWiseSalesReport extends BaseModel
{
    protected $table = 'salesmen';

    protected $guarded = [];
    

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $order = ['sm.id' => 'asc'];
    protected $_warehouse_id; 
    protected $_salesmen_id; 
    protected $_start_date; 
    protected $_end_date; 

    //methods to set custom search property value
    public function setWarehouseID($warehouse_id)
    {
        $this->_warehouse_id = $warehouse_id;
    }
    public function setSalesmanID($salesmen_id)
    {
        $this->_salesmen_id = $salesmen_id;
    }
    public function setStartDate($start_date)
    {
        $this->_start_date = $start_date;
    }
    public function setEndDate($end_date)
    {
        $this->_end_date = $end_date;
    }

    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)

        $this->column_order = [null,'s.warehouse_id','sm.name', null, null];
        
        
        $query = DB::table('salesmen as sm')
                ->selectRaw("SUM(s.grand_total) as total_amount, SUM(s.item) as total_item,SUM(s.total_qty) as total_qty, sm.name, sm.phone, w.name as warehouse_name")
                ->rightjoin('sales as s','sm.id','=','s.salesmen_id')
                ->rightjoin('warehouses as w','s.warehouse_id','=','w.id')
                ->groupBy('s.salesmen_id')
                ->where('s.warehouse_id', auth()->user()->warehouse->id);

        if (!empty($this->_salesmen_id)) {
            $query->where('s.salesmen_id', $this->_salesmen_id);
        }
        if (!empty($this->_start_date) && !empty($this->_end_date)) {
            $query->whereDate('s.sale_date', '>=',$this->_start_date)
            ->whereDate('s.sale_date', '<=',$this->_end_date);
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
        return DB::table('sales as s')
        ->selectRaw("SUM(s.grand_total) as total_amount, COUNT(s.memo_no) as total_invoice, sm.name, sm.phone")
        ->leftjoin('salesmen as sm','s.salesmen_id','=','sm.id')
        ->groupBy('s.salesmen_id')
        ->where('s.warehouse_id', auth()->user()->warehouse->id)
        ->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
