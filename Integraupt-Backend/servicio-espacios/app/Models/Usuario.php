<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Usuario extends Model
{
    protected $table = 'usuario';
    protected $primaryKey = 'IdUsuario';
    public $timestamps = false;

    protected $fillable = [
        'Nombre', 'Apellido', 'TipoDoc', 'NumDoc',
        'Rol', 'Celular', 'Genero', 'Estado', 'FechaRegistro',
    ];

    protected $casts = [
        'Genero'        => 'boolean',
        'Estado'        => 'integer',
        'FechaRegistro' => 'datetime',
    ];

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'Rol', 'IdRol');
    }

    public function tipoDocumento(): BelongsTo
    {
        return $this->belongsTo(TipoDocumento::class, 'TipoDoc', 'IdTipoDoc');
    }

    public function auth(): HasOne
    {
        return $this->hasOne(UsuarioAuth::class, 'IdUsuario', 'IdUsuario');
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class, 'usuario', 'IdUsuario');
    }

    public function sanciones(): HasMany
    {
        return $this->hasMany(Sancion::class, 'Usuario', 'IdUsuario');
    }
}
