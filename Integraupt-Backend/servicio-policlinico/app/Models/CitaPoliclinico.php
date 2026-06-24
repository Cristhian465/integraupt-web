<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CitaPoliclinico extends Model
{
    protected $table = 'cita_policlinico';
    protected $primaryKey = 'IdCita';
    public $timestamps = false;

    protected $fillable = [
        'Estudiante',
        'Medico',
        'TipoAtencion',
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

    public function medico()
    {
        return $this->belongsTo(Medico::class, 'Medico', 'IdMedico');
    }

    public function tipoAtencion()
    {
        return $this->belongsTo(TipoAtencion::class, 'TipoAtencion', 'IdTipoAtencion');
    }

    public function bloque()
    {
        return $this->belongsTo(BloqueHorario::class, 'Bloque', 'IdBloque');
    }
}
