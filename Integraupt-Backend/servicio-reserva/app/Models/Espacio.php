<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function estaActivo(): bool
    {
        return (int) $this->Estado === 1;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->IdEspacio,
            'codigo' => $this->Codigo,
            'nombre' => $this->Nombre,
            'tipo' => $this->Tipo,
            'capacidad' => $this->Capacidad,
            'equipamiento' => $this->Equipamiento,
            'escuelaId' => $this->Escuela,
            'estado' => $this->Estado,
        ];
    }
}
