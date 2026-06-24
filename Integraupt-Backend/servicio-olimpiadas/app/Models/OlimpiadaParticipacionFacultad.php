<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OlimpiadaParticipacionFacultad extends Model
{
    protected $table = 'olimpiada_participacion_facultad';

    protected $primaryKey = 'IdParticipacion';

    public $timestamps = false;

    protected $fillable = [
        'EdicionDisciplina',
        'Facultad',
        'PartidosJugados',
        'PartidosGanados',
        'PartidosEmpatados',
        'PartidosPerdidos',
        'PuntosAFavor',
        'PuntosEnContra',
        'Puntos',
        'Posicion',
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
