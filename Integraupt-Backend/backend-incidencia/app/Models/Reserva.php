<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    use HasFactory;

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
        'Estado'
    ];

    public function incidencias()
    {
        return $this->hasMany(Incidencia::class, 'reserva_id');
    }

    public function espacioRelacion()
    {
        return $this->belongsTo(
            Espacio::class,
            'espacio'
        );
    }

    public function bloqueRelacion()
    {
        return $this->belongsTo(
            BloqueHorario::class,
            'bloque',
            'IdBloque'
        );
    }
}