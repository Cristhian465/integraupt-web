<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoDocumento extends Model
{
    protected $table = 'tipodocumento';
    protected $primaryKey = 'idTipoDoc';
    public $timestamps = false;
    protected $guarded = [];
}
