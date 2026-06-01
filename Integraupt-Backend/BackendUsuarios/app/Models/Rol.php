<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends BaseModel
{
    protected $table = 'rol';
    protected $primaryKey = 'IdRol';
    public $timestamps = false;
    protected $guarded = [];
}
