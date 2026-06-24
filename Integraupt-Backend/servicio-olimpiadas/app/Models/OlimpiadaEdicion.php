<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OlimpiadaEdicion extends Model
{
    protected $table = 'olimpiada_edicion';

    protected $primaryKey = 'IdEdicion';

    public $timestamps = false;

    protected $fillable = [
        'Nombre',
        'AnioInicio',
        'SemestreInicio',
        'AnioFin',
        'SemestreFin',
        'Estado',
        'FechaAperturaInscripcion',
        'FechaCierreInscripcion',
        'FechaInicioJuegos',
        'FechaFinJuegos',
        'Observaciones',
    ];

    public function disciplinas()
    {
        return $this->hasMany(OlimpiadaEdicionDisciplina::class, 'Edicion', 'IdEdicion');
    }

    public function inscripcionAbierta(): bool
    {
        return $this->Estado === 'inscripcion_abierta';
    }
}
