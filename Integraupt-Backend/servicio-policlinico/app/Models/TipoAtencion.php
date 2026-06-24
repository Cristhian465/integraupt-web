<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoAtencion extends Model
{
    protected $table = 'tipo_atencion';
    protected $primaryKey = 'IdTipoAtencion';
    public $timestamps = false;

    protected $fillable = [
        'Nombre',
        'Estado',
    ];

    protected $casts = [
        'Estado' => 'boolean',
    ];
}
