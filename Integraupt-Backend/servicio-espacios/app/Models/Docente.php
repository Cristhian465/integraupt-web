<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Docente extends Model
{
    protected $table = 'docente';
    protected $primaryKey = 'IdDocente';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'IdDocente', 'IdUsuario', 'CodigoDocente',
        'Escuela', 'TipoContrato', 'Especialidad', 'FechaIncorporacion',
    ];

    protected $casts = ['FechaIncorporacion' => 'date'];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'IdUsuario', 'IdUsuario');
    }

    public function escuela(): BelongsTo
    {
        return $this->belongsTo(Escuela::class, 'Escuela', 'IdEscuela');
    }
}
