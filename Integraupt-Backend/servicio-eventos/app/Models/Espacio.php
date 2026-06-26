<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Espacio extends Model
{
    protected $table = 'espacio';

    protected $primaryKey = 'IdEspacio';

    public $timestamps = false;

    protected $fillable = [
        'Codigo',
        'Nombre',
        'Tipo',
        'Capacidad',
        'Equipamiento',
        'Escuela',
        'Estado',
    ];

    protected $casts = [
        'Capacidad' => 'integer',
        'Estado' => 'integer',
        'Escuela' => 'integer',
    ];

    public function escuela()
    {
        return $this->belongsTo(Escuela::class, 'Escuela', 'IdEscuela');
    }
}
