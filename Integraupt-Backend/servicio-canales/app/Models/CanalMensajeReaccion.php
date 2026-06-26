<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CanalMensajeReaccion extends Model
{
    protected $table = 'canal_mensaje_reaccion';

    protected $primaryKey = 'IdReaccion';

    public $timestamps = false;

    protected $fillable = [
        'IdMensaje',
        'IdUsuario',
        'Emoji',
    ];

    protected $casts = [
        'FechaReaccion' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'IdUsuario', 'IdUsuario');
    }
}
