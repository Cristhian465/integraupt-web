<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    protected $table = 'estudiante';
    protected $primaryKey = 'IdEstudiante';
    public $timestamps = false;

    protected $fillable = [
        'IdUsuario',
        'Codigo',
        'Escuela',
    ];

    public function escuelaRel()
    {
        return $this->belongsTo(Escuela::class, 'Escuela', 'IdEscuela');
    }
}
