<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ASMModule extends Model
{
    protected $table = 'asm_module';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['module_id','asm_id'];
}
