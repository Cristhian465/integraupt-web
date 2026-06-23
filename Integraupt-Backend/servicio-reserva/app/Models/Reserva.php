<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    protected $table = 'reserva';

    protected $primaryKey = 'IdReserva';

    public $timestamps = false;

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

    protected $casts = [
        'fechaReserva' => 'date',
        'fechaSolicitud' => 'datetime',
    ];

    public function toArray(): array
    {
        return [
            'id' => $this->IdReserva,
            'usuarioId' => $this->usuario,
            'espacioId' => $this->espacio,
            'bloqueId' => $this->bloque,
            'cursoId' => $this->curso,
            'fechaReserva' => $this->fechaReserva?->format('Y-m-d'),
            'fechaSolicitud' => $this->fechaSolicitud?->format('Y-m-d\TH:i:s'),
            'descripcionUso' => $this->DescripcionUso,
            'cantidadEstudiantes' => $this->CantidadEstudiantes,
            'estado' => $this->Estado,
        ];
    }
}
