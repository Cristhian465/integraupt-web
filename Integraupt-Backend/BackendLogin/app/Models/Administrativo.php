<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Administrativo extends Model
{
    protected $table = 'administrativo';
    protected $primaryKey = 'idAdministrativo';
    public $timestamps = false;

    protected $guarded = [];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario', 'idUsuario');
    }

    public function escuela()
    {
        return $this->belongsTo(Escuela::class, 'idEscuela', 'idEscuela');
    }
}
