<?php

namespace App\Interfaces;

use App\Models\Espacio;
use Illuminate\Database\Eloquent\Collection;

/**
 * Replica: IEspacioRepositorio extends JpaRepository<Espacio, Integer>
 */
interface EspacioRepositoryInterface
{
    public function all(): Collection;

    public function findById(int $id): ?Espacio;

    public function create(array $data): Espacio;

    public function update(int $id, array $data): Espacio;

    public function delete(int $id): bool;

    /**
     * Replica: Optional<Espacio> findByCodigoIgnoreCase(String codigo)
     */
    public function findByCodigoIgnoreCase(string $codigo): ?Espacio;
}
