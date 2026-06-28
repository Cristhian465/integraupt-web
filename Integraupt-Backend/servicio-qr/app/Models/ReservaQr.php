<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservaQr extends Model
{
    protected $table = 'reserva_qr';

    public $timestamps = false;

    protected $fillable = [
        'token',
        'reserva_id',
        'laboratorio',
        'fecha',
        'hora',
        'estado',
        'solicitante_nombre',
        'solicitante_codigo',
        'generado_en',
        'verificado_en',
    ];

    protected $casts = [
        'generado_en' => 'datetime',
        'verificado_en' => 'datetime',
    ];
}
