<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incidencia extends Model
{
    protected $table = 'incidencia';
    protected $primaryKey = 'IdIncidencia';
    public $timestamps = false;

    protected $fillable = ['Reserva', 'Descripcion', 'FechaReporte'];

    protected $casts = ['FechaReporte' => 'datetime'];

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class, 'Reserva', 'IdReserva');
    }
}
