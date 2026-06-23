<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incidencia extends Model
{
    use HasFactory;

    protected $table = 'incidencia';

    protected $primaryKey = 'IdIncidencia';

    public $timestamps = false;

    protected $fillable = [
        'Reserva',
        'Descripcion',
        'FechaReporte',
    ];

    protected $casts = [
        'FechaReporte' => 'datetime',
    ];

    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'Reserva', 'IdReserva');
    }
}
