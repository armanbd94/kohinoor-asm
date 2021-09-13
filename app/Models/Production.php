<?php
namespace App\Models;

use App\Models\BaseModel;
use App\Models\Warehouse;

class Production extends BaseModel
{
    protected $fillable = ['batch_no', 'warehouse_id', 'start_date', 'end_date', 'item', 'status', 'production_status',
    'transfer_status', 'transfer_date', 'created_by', 'modified_by'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class,'warehouse_id','id');
    }

    
}
