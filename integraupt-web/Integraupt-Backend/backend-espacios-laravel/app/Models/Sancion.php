<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sancion extends Model
{
    protected $table = 'sancion';
    protected $primaryKey = 'IdSancion';
    public $timestamps = false;

    protected $fillable = [
        'Usuario', 'Motivo', 'FechaInicio',
        'FechaFin', 'Estado', 'TipoUsuario',
    ];

    protected $casts = [
        'FechaInicio' => 'date',
        'FechaFin'    => 'date',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'Usuario', 'IdUsuario');
    }
}
