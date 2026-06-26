<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    protected $table = 'evento';

    protected $primaryKey = 'IdEvento';

    public $timestamps = false;

    public const ALCANCE_FACULTAD = 'facultad';
    public const ALCANCE_ESCUELA = 'escuela';

    public const ESTADO_BORRADOR = 'borrador';
    public const ESTADO_PUBLICADO = 'publicado';
    public const ESTADO_EN_CURSO = 'en_curso';
    public const ESTADO_FINALIZADO = 'finalizado';
    public const ESTADO_CANCELADO = 'cancelado';

    protected $fillable = [
        'Titulo',
        'Descripcion',
        'ImagenUrl',
        'TipoEvento',
        'Alcance',
        'IdFacultad',
        'IdEscuela',
        'IdEspacio',
        'FechaInicio',
        'FechaFin',
        'AforoMaximo',
        'RequiereInscripcion',
        'Estado',
        'IdResponsable',
    ];

    protected $casts = [
        'FechaInicio' => 'datetime',
        'FechaFin' => 'datetime',
        'FechaCreacion' => 'datetime',
        'RequiereInscripcion' => 'boolean',
        'AforoMaximo' => 'integer',
    ];

    public function facultad()
    {
        return $this->belongsTo(Facultad::class, 'IdFacultad', 'IdFacultad');
    }

    public function escuela()
    {
        return $this->belongsTo(Escuela::class, 'IdEscuela', 'IdEscuela');
    }

    public function espacio()
    {
        return $this->belongsTo(Espacio::class, 'IdEspacio', 'IdEspacio');
    }

    public function responsable()
    {
        return $this->belongsTo(Usuario::class, 'IdResponsable', 'IdUsuario');
    }

    public function inscripciones()
    {
        return $this->hasMany(EventoInscripcion::class, 'IdEvento', 'IdEvento');
    }
}
