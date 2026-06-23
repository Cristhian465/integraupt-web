<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Escuela extends Model
{
    protected $table = 'escuela';
    protected $primaryKey = 'idEscuela';
    public $timestamps = false;
    protected $guarded = [];
}
