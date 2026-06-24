<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuario';
    protected $primaryKey = 'IdUsuario';
    public $timestamps = false;

    protected $fillable = [
        'Nombre',
        'Apellido',
    ];

    public function estudiante()
    {
        return $this->hasOne(Estudiante::class, 'IdUsuario', 'IdUsuario');
    }
}
