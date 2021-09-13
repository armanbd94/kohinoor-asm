<?php
namespace Modules\Report\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

class DailyClosing extends BaseModel
{
    protected $fillable = [ 'warehouse_id','last_day_closing', 'cash_in', 'cash_out', 'balance','transfer','closing_amount', 'adjustment', 'date','thousands', 'five_hundred', 'hundred', 'fifty', 'twenty', 'ten', 'five', 'two', 'one', 'created_by', 'modified_by'];

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $order = ['dc.id' => 'desc'];
    protected $start_date; 
    protected $end_date; 

    //methods to set custom search property value
    public function setStartDate($start_date)
    {
        $this->start_date = $start_date;
    }
    public function setEndDate($end_date)
    {
        $this->end_date = $end_date;
    }

    private function get_datatable_query()
    {
        //set column sorting index table column name wise (should match with frontend table header)

        $this->column_order = ['dc.id','dc.date', 'dc.cash_in','dc.cash_out','dc.balance','dc.transfer','dc.closing_amount'];
        
        
        $query = DB::table('daily_closings as dc')
        ->join('warehouses as w','dc.warehouse_id','=','w.id')
        ->select('dc.*','w.name')
        ->where('dc.warehouse_id',auth()->user()->warehouse->id);

        //search query
        if (!empty($this->start_date)) {
            $query->where('dc.date', '>=',$this->start_date);
        }
        if (!empty($this->end_date)) {
            $query->where('dc.date', '<=',$this->end_date);
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
        return self::toBase()->where('warehouse_id',auth()->user()->warehouse->id)->get()->count();
    }
    /******************************************
     * * * End :: Custom Datatable Code * * *
    *******************************************/
}
