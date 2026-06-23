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
        'IdUsuario',
        'Codigo',
        'Escuela'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'IdUsuario', 'IdUsuario');
    }

    public function escuela()
    {
        return $this->belongsTo(Escuela::class, 'Escuela', 'IdEscuela');
    }
}
