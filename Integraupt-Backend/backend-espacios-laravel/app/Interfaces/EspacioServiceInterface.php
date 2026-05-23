<?php

namespace App\Interfaces;

use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Resources\EspacioResource;

/**
 * Replica: IEspacioServicio.java
 */
interface EspacioServiceInterface
{
    public function listar(): array;

    public function buscarPorId(int $id): EspacioResource;

    public function crear(array $data): EspacioResource;

    public function actualizar(int $id, array $data): EspacioResource;

    public function eliminar(int $id): void;
}
