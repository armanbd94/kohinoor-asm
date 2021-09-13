<?php

namespace App\Models;

use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\User as Authenticatable;

class ASM extends Authenticatable
{
    protected $table = 'asms';
    protected $fillable = [
        'name','username','email','phone','avatar','password','district_id','address','nid_no','monthly_target_value','status','created_by','modified_by'
    ];

    protected $hidden = [
        'password',
        'remember_token',  
    ];

    public function warehouse()
    {
        return $this->hasOne(Warehouse::class,'asm_id','id')->withDefault(['name'=>'-']);
    }
    public function district()
    {
        return $this->belongsTo(District::class,'district_id','id');
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function district_id_wise_asm_list(int $id)
    {
        return self::where('district_id',$id)->pluck('name','id');
    }

    public function module_asm(){
        return $this->belongsToMany(Module::class,ASMModule::class,'asm_id','module_id','id','id')->withTimestamps();
    }

    public function permission_asm(){
        return $this->belongsToMany(Permission::class,ASMPermission::class,'asm_id','permission_id','id','id')->withTimestamps();
    }
}
