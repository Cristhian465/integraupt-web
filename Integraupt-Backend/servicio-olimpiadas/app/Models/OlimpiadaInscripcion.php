<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OlimpiadaInscripcion extends Model
{
    protected $table = 'olimpiada_inscripcion';

    protected $primaryKey = 'IdInscripcion';

    public $timestamps = false;

    protected $fillable = [
        'EdicionDisciplina',
        'Usuario',
        'Facultad',
        'Estado',
        'Observaciones',
        'FechaInscripcion',
    ];

    public function edicionDisciplina()
    {
        return $this->belongsTo(OlimpiadaEdicionDisciplina::class, 'EdicionDisciplina', 'IdEdicionDisciplina');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'Usuario', 'IdUsuario');
    }

    public function facultad()
    {
        return $this->belongsTo(Facultad::class, 'Facultad', 'IdFacultad');
    }
}
