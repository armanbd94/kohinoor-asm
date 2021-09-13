<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ASMPermission extends Model
{
    protected $table = 'asm_permission';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['permission_id','asm_id'];
}
