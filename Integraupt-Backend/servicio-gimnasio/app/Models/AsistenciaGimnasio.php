<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenciaGimnasio extends Model
{
    protected $table = 'asistencia_gimnasio';
    protected $primaryKey = 'IdAsistencia';
    public $timestamps = false;

    protected $fillable = [
        'IdUsuario',
        'FechaIngreso',
        'FechaSalida',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'IdUsuario', 'IdUsuario');
    }
}
