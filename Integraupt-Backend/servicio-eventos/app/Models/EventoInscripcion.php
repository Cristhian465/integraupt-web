<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventoInscripcion extends Model
{
    protected $table = 'evento_inscripcion';

    protected $primaryKey = 'IdInscripcion';

    public $timestamps = false;

    public const TIPO_ESTUDIANTE = 'estudiante';
    public const TIPO_DOCENTE = 'docente';

    public const ESTADO_INSCRITO = 'inscrito';
    public const ESTADO_ASISTIO = 'asistio';
    public const ESTADO_NO_ASISTIO = 'no_asistio';
    public const ESTADO_CANCELADO = 'cancelado';

    protected $fillable = [
        'IdEvento',
        'IdUsuario',
        'TipoUsuario',
        'Estado',
        'CodigoQr',
    ];

    protected $casts = [
        'FechaInscripcion' => 'datetime',
    ];

    public function evento()
    {
        return $this->belongsTo(Evento::class, 'IdEvento', 'IdEvento');
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'IdUsuario', 'IdUsuario');
    }

    public function certificado()
    {
        return $this->hasOne(EventoCertificado::class, 'IdInscripcion', 'IdInscripcion');
    }
}
