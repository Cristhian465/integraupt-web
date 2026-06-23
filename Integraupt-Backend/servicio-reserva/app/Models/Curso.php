<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    protected $table = 'cursos';

    protected $primaryKey = 'IdCurso';

    public $timestamps = false;

    protected $fillable = [
        'Nombre',
        'Facultad',
        'Escuela',
        'Ciclo',
        'Estado',
    ];

    public function estaActivo(): bool
    {
        return (int) $this->Estado === 1;
    }
}
