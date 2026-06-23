<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Espacio extends Model
{
    use HasFactory;

    protected $table = 'espacio';

    protected $fillable = [
        'codigo',
        'nombre'
    ];

    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'espacio_id');
    }
}