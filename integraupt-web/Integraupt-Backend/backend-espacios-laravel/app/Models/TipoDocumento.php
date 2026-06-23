<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoDocumento extends Model
{
    protected $table = 'tipodocumento';
    protected $primaryKey = 'IdTipoDoc';
    public $timestamps = false;

    protected $fillable = ['Nombre', 'Abreviatura'];
}
