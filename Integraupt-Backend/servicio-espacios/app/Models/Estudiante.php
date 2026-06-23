<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Estudiante extends Model
{
    protected $table = 'estudiante';
    protected $primaryKey = 'IdEstudiante';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = ['IdEstudiante', 'IdUsuario', 'Codigo', 'Escuela'];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'IdUsuario', 'IdUsuario');
    }

    public function escuela(): BelongsTo
    {
        return $this->belongsTo(Escuela::class, 'Escuela', 'IdEscuela');
    }
}
