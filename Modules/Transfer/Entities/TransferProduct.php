<?php

namespace Modules\Transfer\Entities;

use Illuminate\Database\Eloquent\Model;

class TransferProduct extends Model
{
    protected $table = 'transfer_products';
    protected $fillable = ['transfer_id', 'product_id', 'unit_qty', 'base_unit_qty', 'net_unit_price', 
    'base_unit_price', 'tax_rate', 'tax', 'total'];

}
