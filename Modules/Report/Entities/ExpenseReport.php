<?php
namespace Modules\Report\Entities;

use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Modules\Expense\Entities\ExpenseItem;
use Modules\Account\Entities\ChartOfAccount;

class ExpenseReport extends BaseModel
{
    protected $table = 'expenses';
    protected $fillable = ['expense_item_id', 'voucher_no', 'amount', 'date', 'payment_type', 'account_id', 'remarks',
    'status','status_change_by','status_change_date', 'created_by', 'modified_by'];

    public function expense_item()
    {
        return $this->belongsTo(ExpenseItem::class);
    }

    public function coa()
    {
        return $this->belongsTo(ChartOfAccount::class,'account_id','id');
    }
    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    //custom search column property
    protected $order = ['e.id' => 'desc'];
    protected $_expense_item_id; 
    protected $_start_date; 
    protected $_end_date; 
    //methods to set custom search property value
    public function setExpenseItemID($expense_item_id)
    {
        $this->_expense_item_id = $expense_item_id;
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

        $this->column_order = ['e.id','e.voucher_no','e.date','e.expense_item_id','e.remarks','e.payment_type','e.account_id','e.amount'];
        
        
        $query = DB::table('expenses as e')
        ->join('expense_items as ei','e.expense_item_id','=','ei.id')
        ->join('chart_of_accounts as coa','e.account_id','=','coa.id')
        ->selectRaw('e.*,ei.name as expense_name,coa.name as account_name')
        ->where('e.warehouse_id',auth()->user()->warehouse->id);

        //search query
        if (!empty($this->_expense_item_id)) {
            $query->where('e.expense_item_id', $this->_expense_item_id);
        }
        if (!empty($this->_start_date)) {
            $query->whereDate('e.date', '>=',$this->_start_date)
                  ->whereDate('e.date', '<=',$this->_end_date);
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
