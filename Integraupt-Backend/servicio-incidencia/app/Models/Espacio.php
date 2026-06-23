<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Espacio extends Model
{
    use HasFactory;

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

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'espacio');
    }
}