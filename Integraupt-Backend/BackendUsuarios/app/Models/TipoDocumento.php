<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoDocumento extends BaseModel
{
    protected $table = 'tipodocumento';
    protected $primaryKey = 'IdTipoDoc';
    public $timestamps = false;
    protected $guarded = [];
}
