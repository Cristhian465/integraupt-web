<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    use HasFactory;

    protected $table = 'estudiante';

    protected $primaryKey = 'IdEstudiante';

    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'nombre',
        'usuario_id'
    ];

    // 🔵 relación con usuario
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'IdUsuario');
    }
}
