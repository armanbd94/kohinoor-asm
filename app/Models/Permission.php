<?php

namespace App\Models;

use App\Models\BaseModel;

class Permission extends BaseModel
{

    protected $fillable = ['module_id','name','slug']; //fillable column name

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function permission_asm() {
        return $this->hasMany(ASMPermission::class);
    }

}
