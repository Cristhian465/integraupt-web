<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sancion extends Model
{
    use HasFactory;

    protected $table = 'sancion';

    protected $primaryKey = 'IdSancion';

    public $timestamps = false; // porque no aparecen en Java

    protected $fillable = [
        'Usuario',
        'TipoUsuario',
        'Motivo',
        'FechaInicio',
        'FechaFin',
        'Estado'
    ];

    protected $casts = [
        'FechaInicio' => 'date',
        'FechaFin' => 'date',
    ];
}