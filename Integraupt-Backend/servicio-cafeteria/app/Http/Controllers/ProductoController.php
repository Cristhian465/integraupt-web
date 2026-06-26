<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductoRequest;
use App\Models\CafeteriaProducto;
use App\Services\ProductoService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function __construct(private ProductoService $productoService)
    {
    }

    public function index(Request $request, $idCafeteria)
    {
        $soloDisponibles = $request->boolean('soloDisponibles');
        $productos = $this->productoService->listarPorCafeteria((int) $idCafeteria, $soloDisponibles)
            ->map(fn (CafeteriaProducto $p) => $this->mapear($p))
            ->all();

        return response()->json($productos);
    }

    public function store(ProductoRequest $request, $idCafeteria)
    {
        $producto = $this->productoService->crear([
            'IdCafeteria' => (int) $idCafeteria,
            'Nombre' => $request->input('nombre'),
            'Descripcion' => $request->input('descripcion'),
            'Precio' => $request->input('precio'),
            'Stock' => $request->input('stock'),
            'Estado' => $request->input('estado', true),
        ]);

        return response()->json($this->mapear($producto), 201);
    }

    public function update(ProductoRequest $request, $idCafeteria, $id)
    {
        try {
            $producto = $this->productoService->actualizar((int) $id, [
                'Nombre' => $request->input('nombre'),
                'Descripcion' => $request->input('descripcion'),
                'Precio' => $request->input('precio'),
                'Stock' => $request->input('stock'),
                'Estado' => $request->input('estado', true),
            ]);

            return response()->json($this->mapear($producto));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Producto no encontrado.'], 404);
        }
    }

    public function cambiarEstado(Request $request, $idCafeteria, $id)
    {
        $request->validate(['estado' => 'required|boolean']);

        try {
            $producto = $this->productoService->cambiarEstado((int) $id, $request->boolean('estado'));
            return response()->json($this->mapear($producto));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Producto no encontrado.'], 404);
        }
    }

    private function mapear(CafeteriaProducto $producto): array
    {
        return [
            'id' => $producto->IdProducto,
            'cafeteriaId' => $producto->IdCafeteria,
            'nombre' => $producto->Nombre,
            'descripcion' => $producto->Descripcion,
            'precio' => (float) $producto->Precio,
            'stock' => $producto->Stock,
            'estado' => (bool) $producto->Estado,
        ];
    }
}
