<?php

namespace App\Repositories;

use App\Interfaces\EspacioRepositoryInterface;
use App\Models\Espacio;
use Illuminate\Database\Eloquent\Collection;

/**
 * Implementación del repositorio de Espacio usando Eloquent.
 * Replica: EspacioRepositorio extends IEspacioRepositorio
 */
class EspacioRepository implements EspacioRepositoryInterface
{
    public function all(): Collection
    {
        return Espacio::with('escuela')->get();
    }

    public function findById(int $id): ?Espacio
    {
        return Espacio::with('escuela')->find($id);
    }

    public function create(array $data): Espacio
    {
        $espacio = Espacio::create($data);
        return $espacio->load('escuela');
    }

    public function update(int $id, array $data): Espacio
    {
        $espacio = Espacio::findOrFail($id);
        $espacio->update($data);
        return $espacio->load('escuela');
    }

    public function delete(int $id): bool
    {
        $espacio = Espacio::findOrFail($id);
        return $espacio->delete();
    }

    /**
     * Replica: findByCodigoIgnoreCase de Spring Data JPA
     */
    public function findByCodigoIgnoreCase(string $codigo): ?Espacio
    {
        return Espacio::whereRaw('LOWER(Codigo) = ?', [strtolower($codigo)])->first();
    }
}
