<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    protected $table = 'usuario';

    protected $primaryKey = 'IdUsuario';

    public $timestamps = false;

    public const ROL_DOCENTE = 1;
    public const ROL_ESTUDIANTE = 2;
    public const ROL_ADMINISTRADOR = 3;
    public const ROL_SUPERVISOR = 4;

    protected $fillable = [
        'Nombre',
        'Apellido',
        'NumDoc',
        'Rol',
    ];

    public function getNombreCompletoAttribute(): string
    {
        return trim(trim($this->Nombre ?? '') . ' ' . trim($this->Apellido ?? ''));
    }

    public function esAdmin(): bool
    {
        return in_array((int) $this->Rol, [self::ROL_ADMINISTRADOR, self::ROL_SUPERVISOR], true);
    }

    public function esDocente(): bool
    {
        return (int) $this->Rol === self::ROL_DOCENTE;
    }
}
