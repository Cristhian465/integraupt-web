<?php

namespace App\Http\Controllers;

use App\Http\Requests\HorarioCursoRequest;
use App\Services\HorarioCursoService;
use App\Services\CatalogoService;
use Illuminate\Http\JsonResponse;

class HorarioCursoController extends Controller
{
    public function __construct(
        protected HorarioCursoService $service,
        protected CatalogoService $catalogoService
    ) {}

    public function listar(): JsonResponse
    {
        return response()->json($this->service->listar());
    }

    public function obtenerPorId(int $id): JsonResponse
    {
        return response()->json($this->service->buscarPorId($id));
    }

    public function crear(HorarioCursoRequest $request): JsonResponse
    {
        $response = $this->service->crear($request->validated());
        return response()->json($response, 201);
    }

    public function actualizar(int $id, HorarioCursoRequest $request): JsonResponse
    {
        $response = $this->service->actualizar($id, $request->validated());
        return response()->json($response);
    }

    public function eliminar(int $id): JsonResponse
    {
        $this->service->eliminar($id);
        return response()->json(null, 204);
    }

    public function obtenerMeta(): JsonResponse
    {
        return response()->json($this->catalogoService->obtenerMeta());
    }
}
