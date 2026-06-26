<?php

namespace App\Services;

use App\Models\CafeteriaProducto;
use Illuminate\Support\Collection;

class ProductoService
{
    public function listarPorCafeteria(int $idCafeteria, bool $soloDisponibles = false): Collection
    {
        $query = CafeteriaProducto::where('IdCafeteria', $idCafeteria)->orderBy('Nombre');

        if ($soloDisponibles) {
            $query->where('Estado', 1)->where('Stock', '>', 0);
        }

        return $query->get();
    }

    public function crear(array $datos): CafeteriaProducto
    {
        return CafeteriaProducto::create($datos)->refresh();
    }

    public function actualizar(int $id, array $datos): CafeteriaProducto
    {
        $producto = CafeteriaProducto::findOrFail($id);
        $producto->update($datos);

        return $producto->refresh();
    }

    public function cambiarEstado(int $id, bool $estado): CafeteriaProducto
    {
        $producto = CafeteriaProducto::findOrFail($id);
        $producto->update(['Estado' => $estado]);

        return $producto->refresh();
    }
}
