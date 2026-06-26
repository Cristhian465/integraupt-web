<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Administrativo extends Model
{
    protected $table = 'administrativo';

    protected $primaryKey = 'IdAdministrativo';

    public $timestamps = false;

    protected $fillable = [
        'IdUsuario',
        'Escuela',
        'Turno',
        'Extension',
        'FechaIncorporacion',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'IdUsuario', 'IdUsuario');
    }
}
