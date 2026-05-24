<?php

namespace App\Models;

use App\Enums\DiaSemana;
use Illuminate\Database\Eloquent\Model;

class HorarioCurso extends Model
{
    protected $table = 'horario_curso';
    protected $primaryKey = 'IdHorarioCurso';
    public $timestamps = false;

    protected $fillable = [
        'Curso',
        'Docente',
        'Espacio',
        'Bloque',
        'DiaSemana',
        'FechaInicio',
        'FechaFin',
        'Estado',
    ];

    protected $casts = [
        'Curso' => 'integer',
        'Docente' => 'integer',
        'Espacio' => 'integer',
        'Bloque' => 'integer',
        'DiaSemana' => DiaSemana::class,
        'FechaInicio' => 'date:Y-m-d',
        'FechaFin' => 'date:Y-m-d',
        'Estado' => 'boolean',
    ];
}
