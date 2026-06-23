<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EspacioRequest;
use App\Http\Resources\EscuelaResource;
use App\Interfaces\EspacioServiceInterface;
use App\Interfaces\EscuelaServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Controlador API para Espacios.
 * Replica exacta de EspacioControlador.java
 *
 * Endpoints:
 * GET    /api/espacios           → index()    (listar)
 * GET    /api/espacios/{id}      → show()     (buscarPorId)
 * GET    /api/espacios/escuelas  → escuelas() (listarEscuelas)
 * POST   /api/espacios           → store()    (crear)
 * PUT    /api/espacios/{id}      → update()   (actualizar)
 * DELETE /api/espacios/{id}      → destroy()  (eliminar)
 */
class EspacioController extends Controller
{
    public function __construct(
        private readonly EspacioServiceInterface $espacioService,
        private readonly EscuelaServiceInterface $escuelaService,
    ) {}

    /**
     * GET /api/espacios
     * Replica: @GetMapping → listar()
     */
    public function index(): JsonResponse
    {
        return response()->json($this->espacioService->listar());
    }

    /**
     * GET /api/espacios/{id}
     * Replica: @GetMapping("/{id}") → buscarPorId()
     */
    public function show(int $id)
    {
        return $this->espacioService->buscarPorId($id);
    }

    /**
     * GET /api/espacios/escuelas
     * Replica: @GetMapping("/escuelas") → listarEscuelas()
     */
    public function escuelas(): JsonResponse
    {
        $escuelas = $this->escuelaService->listar();
        return response()->json($escuelas->map(fn($e) => (new EscuelaResource($e))->resolve())->toArray());
    }

    /**
     * POST /api/espacios
     * Replica: @PostMapping → crear()
     * Retorna 201 Created.
     */
    public function store(EspacioRequest $request): JsonResponse
    {
        $recurso = $this->espacioService->crear($request->toModelData());
        return response()->json($recurso, 201);
    }

    /**
     * PUT /api/espacios/{id}
     * Replica: @PutMapping("/{id}") → actualizar()
     */
    public function update(int $id, EspacioRequest $request)
    {
        return $this->espacioService->actualizar($id, $request->toModelData());
    }

    /**
     * DELETE /api/espacios/{id}
     * Replica: @DeleteMapping("/{id}") → eliminar()
     * Retorna 204 No Content.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->espacioService->eliminar($id);
        return response()->json(null, 204);
    }
}
