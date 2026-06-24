<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OlimpiadaResultadoPosicion extends Model
{
    protected $table = 'olimpiada_resultado_posicion';

    protected $primaryKey = 'IdResultadoPosicion';

    public $timestamps = false;

    protected $fillable = [
        'EdicionDisciplina',
        'Facultad',
        'Posicion',
        'Puntos',
        'Prueba',
        'Fecha',
        'Lugar',
        'Observaciones',
        'Estado',
    ];

    public function edicionDisciplina()
    {
        return $this->belongsTo(OlimpiadaEdicionDisciplina::class, 'EdicionDisciplina', 'IdEdicionDisciplina');
    }

    public function facultad()
    {
        return $this->belongsTo(Facultad::class, 'Facultad', 'IdFacultad');
    }
}
