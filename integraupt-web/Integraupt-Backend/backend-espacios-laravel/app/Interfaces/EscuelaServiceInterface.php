<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;

/**
 * Replica: IEscuelaServicio.java
 */
interface EscuelaServiceInterface
{
    public function listar(): Collection;
}
