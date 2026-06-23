<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OlimpiadaDisciplina extends Model
{
    protected $table = 'olimpiada_disciplina';

    protected $primaryKey = 'IdDisciplina';

    public $timestamps = false;

    protected $fillable = [
        'Nombre',
        'Descripcion',
        'TipoParticipacion',
        'Reglas',
        'CupoMaximoDefault',
        'Estado',
    ];

    public function edicionDisciplinas()
    {
        return $this->hasMany(OlimpiadaEdicionDisciplina::class, 'Disciplina', 'IdDisciplina');
    }
}
