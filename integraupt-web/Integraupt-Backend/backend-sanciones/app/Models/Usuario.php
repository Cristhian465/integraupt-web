<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{
    use HasFactory;

    protected $table = 'usuario';

    protected $primaryKey = 'IdUsuario';

    public $timestamps = false;

    protected $fillable = [
        'Nombre',
        'Apellido',
        'NumDoc'
    ];

    // 🔵 relación con sanciones
    public function sanciones()
    {
        return $this->hasMany(Sancion::class, 'Usuario', 'IdUsuario');
    }

    // 🔵 accessor equivalente a getNombreCompleto()
    public function getNombreCompletoAttribute()
    {
        $nombre = trim($this->Nombre ?? '');
        $apellido = trim($this->Apellido ?? '');

        return trim($nombre . ' ' . $apellido);
    }
}