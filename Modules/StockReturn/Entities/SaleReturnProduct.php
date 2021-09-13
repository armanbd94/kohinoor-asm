<?php

namespace Modules\StockReturn\Entities;

use Illuminate\Database\Eloquent\Model;
use Modules\Product\Entities\Product;

class SaleReturnProduct extends Model
{
    protected $fillable = ['sale_return_id', 'memo_no', 'product_id', 'return_qty', 'unit_id', 'product_rate', 
    'deduction_rate', 'deduction_amount', 'total'];

     public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
