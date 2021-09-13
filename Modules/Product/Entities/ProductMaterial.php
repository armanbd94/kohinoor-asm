<?php

namespace Modules\Product\Entities;

use Illuminate\Database\Eloquent\Model;

class ProductMaterial extends Model
{
    protected $fillable = ['product_id', 'material_id'];

    protected $table = 'product_material';
}
