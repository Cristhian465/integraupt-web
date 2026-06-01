<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends BaseModel
{
    protected $table = 'usuario';
    protected $primaryKey = 'IdUsuario';
    public $timestamps = false; // Assuming false unless verified

    protected $guarded = [];

    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'TipoDoc', 'IdTipoDoc');
    }

    public function rolObj()
    {
        return $this->belongsTo(Rol::class, 'Rol', 'IdRol');
    }

    public function auth()
    {
        return $this->hasOne(UsuarioAuth::class, 'IdUsuario', 'IdUsuario');
    }

    public function estudiante()
    {
        return $this->hasOne(Estudiante::class, 'IdUsuario', 'IdUsuario');
    }

    public function docente()
    {
        return $this->hasOne(Docente::class, 'IdUsuario', 'IdUsuario');
    }

    public function administrativo()
    {
        return $this->hasOne(Administrativo::class, 'IdUsuario', 'IdUsuario');
    }
}
