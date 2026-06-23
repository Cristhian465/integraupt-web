<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditoriaReserva extends Model
{
    protected $table = 'auditoriareserva';
    protected $primaryKey = 'IdAudit';
    public $timestamps = false;

    protected $fillable = [
        'IdReserva',
        'EstadoAnterior',
        'EstadoNuevo',
        'FechaCambio',
        'UsuarioCambio'
    ];

    protected $casts = [
        'FechaCambio' => 'datetime',
    ];
}
