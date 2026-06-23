<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Administrativo extends Model
{
    protected $table = 'administrativo';
    protected $primaryKey = 'IdAdministrativo';
    public $timestamps = false;

    protected $fillable = [
        'IdUsuario', 'Escuela', 'Turno',
        'Extension', 'FechaIncorporacion',
    ];

    protected $casts = ['FechaIncorporacion' => 'date'];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'IdUsuario', 'IdUsuario');
    }

    public function escuela(): BelongsTo
    {
        return $this->belongsTo(Escuela::class, 'Escuela', 'IdEscuela');
    }
}
