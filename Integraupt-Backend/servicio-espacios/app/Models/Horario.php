<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Horario extends Model
{
    protected $table = 'horarios';
    protected $primaryKey = 'IdHorario';
    public $timestamps = false;

    protected $fillable = ['espacio', 'bloque', 'diaSemana', 'ocupado'];

    protected $casts = ['ocupado' => 'boolean'];

    public function espacioRelation(): BelongsTo
    {
        return $this->belongsTo(Espacio::class, 'espacio', 'IdEspacio');
    }

    public function bloqueRelation(): BelongsTo
    {
        return $this->belongsTo(BloqueHorario::class, 'bloque', 'IdBloque');
    }
}
