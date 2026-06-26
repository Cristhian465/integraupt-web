<?php

namespace App\Http\Controllers;

use App\Http\Requests\CafeteriaRequest;
use App\Models\Cafeteria;
use App\Services\CafeteriaService;
use App\Services\CatalogoService;
use App\Services\ProductoService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use InvalidArgumentException;

class CafeteriaController extends Controller
{
    public function __construct(
        private CafeteriaService $cafeteriaService,
        private ProductoService $productoService,
        private CatalogoService $catalogoService
    ) {
    }

    public function index()
    {
        $cafeterias = $this->cafeteriaService->listar()->map(fn (Cafeteria $c) => $this->mapear($c))->all();

        return response()->json($cafeterias);
    }

    public function show($id)
    {
        try {
            return response()->json($this->mapear($this->cafeteriaService->encontrar((int) $id), true));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Cafeteria no encontrada.'], 404);
        }
    }

    public function store(CafeteriaRequest $request)
    {
        try {
            $cafeteria = $this->cafeteriaService->crear($this->aPascalCase($request->validated()));
            return response()->json($this->mapear($cafeteria), 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function update(CafeteriaRequest $request, $id)
    {
        try {
            $cafeteria = $this->cafeteriaService->actualizar((int) $id, $this->aPascalCase($request->validated()));
            return response()->json($this->mapear($cafeteria));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Cafeteria no encontrada.'], 404);
        }
    }

    public function deEncargado(Request $request)
    {
        $usuarioId = (int) $request->query('usuarioId');
        $cafeteria = $this->cafeteriaService->deEncargado($usuarioId);

        if (! $cafeteria) {
            return response()->json(['message' => 'Este usuario no es encargado de ninguna cafeteria.'], 404);
        }

        return response()->json($this->mapear($cafeteria));
    }

    public function delCliente(Request $request)
    {
        $usuarioId = (int) $request->query('usuarioId');
        $facultadId = $this->catalogoService->resolverFacultadDeUsuario($usuarioId);

        if (! $facultadId) {
            return response()->json(['message' => 'No se pudo determinar tu facultad.'], 404);
        }

        $cafeteria = $this->cafeteriaService->porFacultad($facultadId);

        if (! $cafeteria) {
            return response()->json(['message' => 'Tu facultad aun no tiene una cafeteria registrada.'], 404);
        }

        return response()->json($this->mapear($cafeteria, true));
    }

    private function aPascalCase(array $datos): array
    {
        return [
            'Nombre' => $datos['nombre'],
            'IdFacultad' => $datos['facultadId'],
            'Estado' => $datos['estado'] ?? true,
            'IdEncargado' => $datos['encargadoId'] ?? null,
        ];
    }

    private function mapear(Cafeteria $cafeteria, bool $conProductos = false): array
    {
        $data = [
            'id' => $cafeteria->IdCafeteria,
            'nombre' => $cafeteria->Nombre,
            'estado' => (bool) $cafeteria->Estado,
            'facultadId' => $cafeteria->IdFacultad,
            'facultadNombre' => $cafeteria->facultad?->Nombre,
            'encargadoId' => $cafeteria->IdEncargado,
            'encargadoNombre' => $cafeteria->encargado?->NombreCompleto,
        ];

        if ($conProductos) {
            $data['productos'] = $this->productoService->listarPorCafeteria($cafeteria->IdCafeteria)
                ->map(fn ($producto) => [
                    'id' => $producto->IdProducto,
                    'nombre' => $producto->Nombre,
                    'descripcion' => $producto->Descripcion,
                    'precio' => (float) $producto->Precio,
                    'stock' => $producto->Stock,
                    'estado' => (bool) $producto->Estado,
                ])
                ->all();
        }

        return $data;
    }
}
