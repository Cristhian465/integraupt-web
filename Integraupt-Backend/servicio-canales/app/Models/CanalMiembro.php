<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CanalMiembro extends Model
{
    protected $table = 'canal_miembro';

    protected $primaryKey = 'IdCanalMiembro';

    public $timestamps = false;

    public const ROL_CREADOR = 'creador';
    public const ROL_MIEMBRO = 'miembro';

    protected $fillable = [
        'IdCanal',
        'IdUsuario',
        'Rol',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'IdUsuario', 'IdUsuario');
    }
}
