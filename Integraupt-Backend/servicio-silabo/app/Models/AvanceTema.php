<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AvanceTema extends Model
{
    protected $table = 'avance_tema';
    protected $primaryKey = 'IdAvance';

    protected $fillable = [
        'SilaboTemaId',
        'DocenteId',
        'HorarioCursoId',
        'Sesion',
        'FechaClase',
        'Comentario',
        'Estado',
        'ObservacionCoordinador',
    ];

    protected $casts = [
        'FechaClase' => 'date',
    ];

    public function tema()
    {
        return $this->belongsTo(SilaboTema::class, 'SilaboTemaId', 'IdTema');
    }
}
