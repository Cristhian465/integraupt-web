<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuarioSesion extends Model
{
    protected $table = 'usuario_sesion';
    protected $primaryKey = 'IdSesion';
    public $timestamps = false;

    protected $fillable = ['IdUsuario', 'Dispositivo', 'IP', 'Activa'];

    protected $casts = ['Activa' => 'boolean'];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'IdUsuario', 'IdUsuario');
    }
}
