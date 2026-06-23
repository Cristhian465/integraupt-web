<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estudiante extends BaseModel
{
    protected $table = 'estudiante';
    protected $primaryKey = 'IdEstudiante';
    public $timestamps = false;

    protected $guarded = [];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'IdUsuario', 'IdUsuario');
    }

    public function escuela()
    {
        return $this->belongsTo(Escuela::class, 'Escuela', 'IdEscuela');
    }
}
