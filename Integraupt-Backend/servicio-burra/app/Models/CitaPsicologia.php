<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CitaPsicologia extends Model
{
    protected $table = 'cita_psicologia';
    protected $primaryKey = 'IdCita';
    public $timestamps = false;

    protected $fillable = [
        'Estudiante',
        'Psicologo',
        'Bloque',
        'Fecha',
        'Motivo',
        'Estado',
        'FechaSolicitud',
    ];

    protected $casts = [
        'Fecha' => 'date:Y-m-d',
        'FechaSolicitud' => 'datetime',
    ];

    public function estudiante()
    {
        return $this->belongsTo(Usuario::class, 'Estudiante', 'IdUsuario');
    }

    public function psicologo()
    {
        return $this->belongsTo(Psicologo::class, 'Psicologo', 'IdPsicologo');
    }

    public function bloque()
    {
        return $this->belongsTo(BloqueHorario::class, 'Bloque', 'IdBloque');
    }
}
