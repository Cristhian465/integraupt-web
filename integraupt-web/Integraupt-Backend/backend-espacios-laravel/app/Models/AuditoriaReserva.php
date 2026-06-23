<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditoriaReserva extends Model
{
    protected $table = 'auditoriareserva';
    protected $primaryKey = 'IdAudit';
    public $timestamps = false;

    protected $fillable = [
        'IdReserva', 'EstadoAnterior', 'EstadoNuevo',
        'FechaCambio', 'UsuarioCambio',
    ];

    protected $casts = ['FechaCambio' => 'datetime'];

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class, 'IdReserva', 'IdReserva');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'UsuarioCambio', 'IdUsuario');
    }
}
