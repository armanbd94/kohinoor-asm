<?php

namespace Modules\Sale\Entities;

use App\Models\Unit;
use Modules\Product\Entities\Product;
use Illuminate\Database\Eloquent\Model;

class SaleProduct extends Model
{
    protected $fillable = ['sale_id', 'batch_no','product_id', 'qty', 'sale_unit_id', 'net_unit_price', 'discount', 'tax_rate', 'tax', 'total'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function sale_unit()
    {
        return $this->belongsTo(Unit::class,'sale_unit_id','id');
    }
}
