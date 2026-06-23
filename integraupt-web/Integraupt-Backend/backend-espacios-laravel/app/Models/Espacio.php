<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'Estado'    => 'integer',
        'Escuela'   => 'integer',
    ];

    /**
     * Relación: Espacio pertenece a una Escuela.
     * Replica: @ManyToOne(fetch = FetchType.LAZY) @JoinColumn(name = "Escuela")
     */
    public function escuela(): BelongsTo
    {
        return $this->belongsTo(Escuela::class, 'Escuela', 'IdEscuela');
    }
}
