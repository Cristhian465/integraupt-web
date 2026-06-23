<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reserva extends Model
{
    protected $table = 'reserva';
    protected $primaryKey = 'IdReserva';
    public $timestamps = false;

    protected $fillable = [
        'usuario', 'espacio', 'bloque', 'curso',
        'fechaReserva', 'fechaSolicitud', 'DescripcionUso',
        'CantidadEstudiantes', 'Estado',
    ];

    protected $casts = [
        'fechaReserva'        => 'date',
        'fechaSolicitud'      => 'datetime',
        'CantidadEstudiantes' => 'integer',
    ];

    public function usuarioRelation(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario', 'IdUsuario');
    }

    public function espacioRelation(): BelongsTo
    {
        return $this->belongsTo(Espacio::class, 'espacio', 'IdEspacio');
    }

    public function bloqueRelation(): BelongsTo
    {
        return $this->belongsTo(BloqueHorario::class, 'bloque', 'IdBloque');
    }

    public function cursoRelation(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'curso', 'IdCurso');
    }
}
