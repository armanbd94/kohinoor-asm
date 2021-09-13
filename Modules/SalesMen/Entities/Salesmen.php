<?php
namespace Modules\SalesMen\Entities;

use App\Models\Warehouse;
use Illuminate\Support\Facades\Hash;
use Modules\Location\Entities\Route;
use Modules\Location\Entities\Upazila;
use Modules\Location\Entities\District;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Salesmen extends Authenticatable
{
    protected $table= 'salesmen';

    protected $fillable = [ 'name', 'username', 'phone','email', 'avatar', 'password',
     'warehouse_id', 'district_id', 'upazila_id','nid_no','monthly_target_value', 'address', 'status', 'created_by', 'modified_by'];

    protected $hidden = [
        'password',
        'remember_token',  
    ];

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
    public function routes()
    {
        return $this->belongsToMany(Route::class,'sales_men_daily_routes','salesmen_id','route_id','id','id')
        ->withPivot('id','day')            
        ->withTimestamps();
    }

    public function setPasswordAttribute($value){
        $this->attributes['password'] = Hash::make($value);
    }

    /******************************************
     * * * Begin :: Custom Datatable Code * * *
    *******************************************/
    protected $order = ['id' => 'desc'];
    protected $column_order;

    protected $orderValue;
    protected $dirValue;
    protected $startVlaue;
    protected $lengthVlaue;

    public function setOrderValue($orderValue)
    {
        $this->orderValue = $orderValue;
    }
    public function setDirValue($dirValue)
    {
        $this->dirValue = $dirValue;
    }
    public function setStartValue($startVlaue)
    {
        $this->startVlaue = $startVlaue;
    }
    public function setLengthValue($lengthVlaue)
    {
        $this->lengthVlaue = $lengthVlaue;
    }

    protected $_name;
    protected $_username;
    protected $_phone;
    protected $_email;
    protected $_upazila_id;
    protected $_status;

    public function setName($name)
    {
        $this->_name = $name;
    }

    public function setUsername($username)
    {
        $this->_username = $username;
    }

    public function setPhone($phone)
    {
        $this->_phone = $phone;
    }

    public function setEmail($email)
    {
        $this->_email = $email;
    }

    public function setUpazilaID($upazila_id)
    {
        $this->_upazila_id = $upazila_id;
    }

    public function setStatus($status)
    {
        $this->_status = $status;
    }

    private function get_datatable_query()
    { 

        $this->column_order = ['id','id','name','username','phone','upazila_id','email','status',null];
        
        

        $query = self::with('upazila')->where(['warehouse_id'=>auth()->user()->warehouse->id,'district_id'=>auth()->user()->district_id]);

        if (!empty($this->name)) {
            $query->where('name', 'like', '%' . $this->name . '%');
        }
        if (!empty($this->username)) {
            $query->where('username', 'like', '%' . $this->username . '%');
        }
        if (!empty($this->phone)) {
            $query->where('phone', 'like', '%' . $this->phone . '%');
        }
        if (!empty($this->email)) {
            $query->where('email', 'like', '%' . $this->email . '%');
        }
        if (!empty($this->_upazila_id)) {
            $query->where('upazila_id', $this->_upazila_id );
        }
        if (!empty($this->status)) {
            $query->where('status', $this->status );
        }

        if (isset($this->orderValue) && isset($this->dirValue)) {
            $query->orderBy($this->column_order[$this->orderValue], $this->dirValue);
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
}
