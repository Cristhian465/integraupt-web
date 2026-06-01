<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    use HasFactory;

    protected $table = 'reserva';

    protected $fillable = [
        'usuario_id',
        'espacio_id',
        'bloque_id',
        'fecha_reserva'
    ];

    public function incidencias()
    {
        return $this->hasMany(Incidencia::class, 'reserva_id');
    }

    public function espacio()
    {
        return $this->belongsTo(Espacio::class, 'espacio_id');
    }

    public function bloque()
    {
        return $this->belongsTo(BloqueHorario::class, 'bloque_id');
    }
}