<?php

namespace App\Services;

use App\Interfaces\EscuelaServiceInterface;
use App\Models\Escuela;
use Illuminate\Database\Eloquent\Collection;

/**
 * Replica: EscuelaServicio.java
 * Servicio simple de solo lectura para listar escuelas.
 */
class EscuelaService implements EscuelaServiceInterface
{
    /**
     * @Transactional(readOnly = true)
     */
    public function listar(): Collection
    {
        return Escuela::all();
    }
}
