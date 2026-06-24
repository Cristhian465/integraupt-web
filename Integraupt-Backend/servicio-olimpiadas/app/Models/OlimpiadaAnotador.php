<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OlimpiadaAnotador extends Model
{
    protected $table = 'olimpiada_anotador';

    protected $primaryKey = 'IdAnotador';

    public $timestamps = false;

    protected $fillable = [
        'EdicionDisciplina',
        'Facultad',
        'NombreJugador',
        'Cantidad',
        'Observaciones',
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
