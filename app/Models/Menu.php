<?php
namespace App\Models;

use App\Models\BaseModel;

class Menu extends BaseModel
{
    protected $fillable = ['menu_name','deletable']; //fillable column name
    
    public function menuItems()
    {
        return $this->hasMany(Module::class)->doesntHave('parent')->orderBy('order','asc');
    }


}
