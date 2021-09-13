<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Support\Facades\Cache;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\WarehouseProduct;

class Category extends BaseModel
{

    protected $fillable = ['name','type','status','created_by','modified_by'];


    public function warehouse_products()
    {
        return $this->hasManyThrough(WarehouseProduct::class, Product::class,'category_id','product_id','id','id');
    }
   
    /***************************************
     * * * Begin :: Model Local Scope * * *
    ****************************************/
    public function scopeMaterialCategory($query)
    {
        return $query->where(['type'=>1,'status'=>1]);
    }

    public function scopeProductCategory($query)
    {
        return $query->where(['type'=>2,'status'=>1]);
    }
    /***************************************
     * * * Begin :: Model Local Scope * * *
    ****************************************/
    /*************************************
    * * *  Begin :: Cache Data * * *
    **************************************/
    protected const MATERIAL_CATEGORY    = '_material_categories';
    protected const PRODUCT_CATEGORY     = '_product_categories';

    public static function allMaterialCategories(){
        return Cache::rememberForever(self::MATERIAL_CATEGORY, function () {
            return self::materialcategory()->orderBy('name','asc')->get();
        });
    }

    public static function allProductCategories(){
        return Cache::rememberForever(self::PRODUCT_CATEGORY, function () {
            return self::productcategory()->orderBy('name','asc')->get();
        });
    }

    public static function flushCategoryCache(){
        Cache::forget(self::MATERIAL_CATEGORY);
        Cache::forget(self::PRODUCT_CATEGORY);
    }

    public static function boot(){
        parent::boot();

        static::updated(function () {
            self::flushCategoryCache();
        });

        static::created(function() {
            self::flushCategoryCache();
        });

        static::deleted(function() {
            self::flushCategoryCache(); 
        });
    }
    /***********************************
    * * *  Begin :: Cache Data * * *
    ************************************/

}
