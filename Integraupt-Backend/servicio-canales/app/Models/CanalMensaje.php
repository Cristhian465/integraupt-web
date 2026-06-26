<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CanalMensaje extends Model
{
    protected $table = 'canal_mensaje';

    protected $primaryKey = 'IdMensaje';

    public $timestamps = false;

    protected $fillable = [
        'IdCanal',
        'IdTema',
        'IdMensajeRespuesta',
        'IdUsuario',
        'Contenido',
        'ImagenUrl',
    ];

    protected $casts = [
        'FechaEnvio' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'IdUsuario', 'IdUsuario');
    }

    public function respuestaA(): BelongsTo
    {
        return $this->belongsTo(CanalMensaje::class, 'IdMensajeRespuesta', 'IdMensaje');
    }

    public function reacciones(): HasMany
    {
        return $this->hasMany(CanalMensajeReaccion::class, 'IdMensaje', 'IdMensaje');
    }
}
