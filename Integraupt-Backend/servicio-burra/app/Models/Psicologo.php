<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Psicologo extends Model
{
    protected $table = 'psicologo';
    protected $primaryKey = 'IdPsicologo';
    public $timestamps = false;

    protected $fillable = [
        'Nombre',
        'Especialidad',
        'Estado',
    ];

    protected $casts = [
        'Estado' => 'boolean',
    ];
}
