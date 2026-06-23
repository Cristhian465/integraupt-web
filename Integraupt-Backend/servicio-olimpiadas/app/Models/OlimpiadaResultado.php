<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OlimpiadaResultado extends Model
{
    protected $table = 'olimpiada_resultado';

    protected $primaryKey = 'IdResultado';

    public $timestamps = false;

    protected $fillable = [
        'EdicionDisciplina',
        'FacultadLocal',
        'FacultadVisitante',
        'Fase',
        'Grupo',
        'FechaPartido',
        'PuntajeLocal',
        'PuntajeVisitante',
        'FacultadGanadora',
        'Estado',
        'Observaciones',
    ];

    public function edicionDisciplina()
    {
        return $this->belongsTo(OlimpiadaEdicionDisciplina::class, 'EdicionDisciplina', 'IdEdicionDisciplina');
    }

    public function facultadLocal()
    {
        return $this->belongsTo(Facultad::class, 'FacultadLocal', 'IdFacultad');
    }

    public function facultadVisitante()
    {
        return $this->belongsTo(Facultad::class, 'FacultadVisitante', 'IdFacultad');
    }

    public function facultadGanadora()
    {
        return $this->belongsTo(Facultad::class, 'FacultadGanadora', 'IdFacultad');
    }
}
