<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Canal extends Model
{
    protected $table = 'canal';

    protected $primaryKey = 'IdCanal';

    public $timestamps = false;

    public const ESTADO_ACTIVO = 'activo';
    public const ESTADO_ARCHIVADO = 'archivado';

    public const TIPO_CREADOR_ADMIN = 'admin';
    public const TIPO_CREADOR_DOCENTE = 'docente';

    protected $fillable = [
        'Nombre',
        'Descripcion',
        'IdCreador',
        'TipoCreador',
        'Estado',
        'Color',
        'FotoUrl',
    ];

    protected $casts = [
        'FechaCreacion' => 'datetime',
    ];

    public function creador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'IdCreador', 'IdUsuario');
    }

    public function miembros(): HasMany
    {
        return $this->hasMany(CanalMiembro::class, 'IdCanal', 'IdCanal');
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(CanalMensaje::class, 'IdCanal', 'IdCanal');
    }

    public function temas(): HasMany
    {
        return $this->hasMany(CanalTema::class, 'IdCanal', 'IdCanal')->orderBy('Orden')->orderBy('FechaCreacion');
    }
}
