<?php

namespace Modules\Product\Entities;

use Modules\Product\Entities\Product;
use Illuminate\Database\Eloquent\Model;
use Modules\Product\Entities\ProductVariant;

class AdjustmentProduct extends Model
{
    protected $table = 'adjustment_products';
    protected $fillable = ['adjustment_id', 'product_id', 'batch_no', 'base_unit_id', 'base_unit_qty',
     'base_unit_price', 'tax_rate', 'tax', 'total'];
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}
