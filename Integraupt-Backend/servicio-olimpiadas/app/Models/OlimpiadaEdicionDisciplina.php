<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OlimpiadaEdicionDisciplina extends Model
{
    protected $table = 'olimpiada_edicion_disciplina';

    protected $primaryKey = 'IdEdicionDisciplina';

    public $timestamps = false;

    protected $fillable = [
        'Edicion',
        'Disciplina',
        'CupoMaximoPorFacultad',
        'ReglasEspecificas',
        'Estado',
    ];

    public function edicion()
    {
        return $this->belongsTo(OlimpiadaEdicion::class, 'Edicion', 'IdEdicion');
    }

    public function disciplina()
    {
        return $this->belongsTo(OlimpiadaDisciplina::class, 'Disciplina', 'IdDisciplina');
    }

    public function inscripciones()
    {
        return $this->hasMany(OlimpiadaInscripcion::class, 'EdicionDisciplina', 'IdEdicionDisciplina');
    }

    public function resultados()
    {
        return $this->hasMany(OlimpiadaResultado::class, 'EdicionDisciplina', 'IdEdicionDisciplina');
    }

    public function participacionesFacultad()
    {
        return $this->hasMany(OlimpiadaParticipacionFacultad::class, 'EdicionDisciplina', 'IdEdicionDisciplina');
    }

    public function inscritosActivos()
    {
        return $this->inscripciones()->where('Estado', 'inscrito')->count();
    }
}
