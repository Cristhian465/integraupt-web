<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HorarioCurso extends Model
{
    protected $table = 'horario_curso';
    protected $primaryKey = 'IdHorarioCurso';
    public $timestamps = false;

    protected $fillable = [
        'Curso', 'Docente', 'Espacio', 'Bloque',
        'DiaSemana', 'FechaInicio', 'FechaFin', 'Estado',
    ];

    protected $casts = [
        'FechaInicio' => 'date',
        'FechaFin'    => 'date',
        'Estado'      => 'boolean',
    ];

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class, 'Curso', 'IdCurso');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'Docente', 'IdUsuario');
    }

    public function espacio(): BelongsTo
    {
        return $this->belongsTo(Espacio::class, 'Espacio', 'IdEspacio');
    }

    public function bloque(): BelongsTo
    {
        return $this->belongsTo(BloqueHorario::class, 'Bloque', 'IdBloque');
    }
}
