<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservaGestion extends Model
{
    protected $table = 'reserva_gestion';
    protected $primaryKey = 'IdGestion';
    public $timestamps = false;

    protected $fillable = [
        'IdReserva', 'UsuarioGestion', 'FechaGestion',
        'Accion', 'Motivo', 'Comentarios',
    ];

    protected $casts = ['FechaGestion' => 'datetime'];

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class, 'IdReserva', 'IdReserva');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'UsuarioGestion', 'IdUsuario');
    }
}
