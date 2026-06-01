<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent para la tabla `horarios`.
 * Replica: com.horarios.model.Horario (JPA Entity)
 */
class Horario extends Model
{
    protected $table = 'horarios';
    protected $primaryKey = 'IdHorario';
    public $timestamps = false;

    protected $fillable = [
        'espacio',
        'bloque',
        'diaSemana',
        'ocupado',
    ];

    protected $casts = [
        'ocupado' => 'boolean',
    ];

    /**
     * Relación con Espacio.
     * Usa nombre distinto al de la columna FK para evitar conflicto.
     */
    public function espacioRelation(): BelongsTo
    {
        return $this->belongsTo(Espacio::class, 'espacio', 'IdEspacio');
    }

    /**
     * Relación con BloqueHorario.
     * Usa nombre distinto al de la columna FK para evitar conflicto.
     */
    public function bloqueRelation(): BelongsTo
    {
        return $this->belongsTo(BloqueHorario::class, 'bloque', 'IdBloque');
    }
}
