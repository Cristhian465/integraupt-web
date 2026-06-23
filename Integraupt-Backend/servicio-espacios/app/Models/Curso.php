<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Curso extends Model
{
    protected $table = 'cursos';
    protected $primaryKey = 'IdCurso';
    public $timestamps = false;

    protected $fillable = ['Nombre', 'Facultad', 'Escuela', 'Ciclo', 'Estado'];

    protected $casts = ['Estado' => 'boolean'];

    public function facultad(): BelongsTo
    {
        return $this->belongsTo(Facultad::class, 'Facultad', 'IdFacultad');
    }

    public function escuela(): BelongsTo
    {
        return $this->belongsTo(Escuela::class, 'Escuela', 'IdEscuela');
    }
}
