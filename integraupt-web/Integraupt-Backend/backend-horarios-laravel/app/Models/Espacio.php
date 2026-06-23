<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Eloquent para la tabla `espacio`.
 * Replica: com.horarios.model.Espacio (JPA Entity)
 */
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
    ];

    protected $casts = [
        'Capacidad' => 'integer',
    ];
}
