<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    use HasFactory;

    protected $table = 'reserva';
    protected $primaryKey = 'IdReserva';

    protected $fillable = [
        'usuario',
        'espacio',
        'bloque',
        'curso',
        'fechaReserva',
        'fechaSolicitud',
        'DescripcionUso',
        'CantidadEstudiantes',
        'Estado',
    ];
}
