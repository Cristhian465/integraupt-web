<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloqueHorario extends Model
{
    use HasFactory;

    protected $table = 'bloque_horario';

    protected $fillable = [
        'hora_inicio',
        'hora_fin'
    ];

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'bloque_id');
    }
}