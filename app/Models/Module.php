<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{

    protected $fillable = ['menu_id', 'type', 'module_name', 'divider_title', 'icon_class', 'url', 'order', 'parent_id', 'target'];
    

    public function menu(){
        return $this->belongsTo(Menu::class);
    }

    public function parent(){
        return $this->belongsTo(Module::class,'parent_id','id')->orderBy('order','asc');
    }

    public function children(){
        $asm_id = auth()->user()->id;
        return $this->hasMany(Module::class,'parent_id','id')
                    ->whereHas('module_asm', function($q) use ($asm_id){
                        $q->where('asm_id',$asm_id);
                    })
                    ->orderBy('order','asc');
    }

    public function submenu(){
        return $this->hasMany(Module::class,'parent_id','id')
        ->orderBy('order','asc')
        ->with('permission:id,module_id,name');
    }

    public function permission(){
        return $this->hasMany(Permission::class);
    }

    public function module_asm() {
        return $this->hasMany(ASMModule::class);
    }


    public static function permission_module_list(int $menu_id){
       return self::doesntHave('parent')
                ->select('id','type','divider_title','module_name','order','icon_class')
                ->orderBy('order','asc')
                ->with('permission:id,module_id,name','submenu:id,parent_id,module_name,icon_class')
                ->where('menu_id',$menu_id)
                ->get();
    }
}
