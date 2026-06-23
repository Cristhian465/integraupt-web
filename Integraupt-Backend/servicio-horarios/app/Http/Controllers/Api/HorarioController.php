<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HorarioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controlador API para Horarios.
 * Replica: com.horarios.controller.HorarioController
 *
 * Endpoints:
 * GET    /api/horarios                           → index()
 * GET    /api/horarios/disponibles               → disponibles()
 * GET    /api/horarios/ocupados                  → ocupados()
 * GET    /api/horarios/espacio/{espacioId}       → porEspacio()
 * GET    /api/horarios/espacio/{espacioId}/semanal → semanal()
 * GET    /api/horarios/dia/{diaSemana}           → porDia()
 * GET    /api/horarios/{id}                      → show()
 * POST   /api/horarios                           → store()
 * PUT    /api/horarios/{id}                      → update()
 * PATCH  /api/horarios/{id}/ocupacion            → actualizarOcupacion()
 * DELETE /api/horarios/{id}                      → destroy()
 */
class HorarioController extends Controller
{
    public function __construct(
        private readonly HorarioService $horarioService,
    ) {}

    /**
     * GET /api/horarios
     * Replica: @GetMapping → listarHorarios()
     */
    public function index(): JsonResponse
    {
        return response()->json($this->horarioService->listarTodos());
    }

    /**
     * GET /api/horarios/{id}
     * Replica: @GetMapping("/{id}") → obtenerHorario()
     */
    public function show(int $id): JsonResponse
    {
        $horario = $this->horarioService->buscarPorId($id);

        if (!$horario) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Horario no encontrado',
            ], 404);
        }

        return response()->json([
            'exito' => true,
            'mensaje' => 'Horario encontrado',
            'horario' => $horario,
        ]);
    }

    /**
     * GET /api/horarios/espacio/{espacioId}
     * Replica: @GetMapping("/espacio/{espacioId}") → listarPorEspacio()
     */
    public function porEspacio(int $espacioId): JsonResponse
    {
        return response()->json($this->horarioService->listarPorEspacio($espacioId));
    }

    /**
     * GET /api/horarios/espacio/{espacioId}/semanal
     * Replica: @GetMapping("/espacio/{espacioId}/semanal") → obtenerHorarioSemanal()
     */
    public function semanal(int $espacioId): JsonResponse
    {
        return response()->json($this->horarioService->obtenerHorarioSemanalPorEspacio($espacioId));
    }

    /**
     * GET /api/horarios/dia/{diaSemana}
     * Replica: @GetMapping("/dia/{diaSemana}") → listarPorDia()
     */
    public function porDia(string $diaSemana): JsonResponse
    {
        try {
            return response()->json($this->horarioService->listarPorDia($diaSemana));
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'exito' => false,
                'mensaje' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * GET /api/horarios/disponibles
     * Replica: @GetMapping("/disponibles") → listarDisponibles()
     */
    public function disponibles(): JsonResponse
    {
        return response()->json($this->horarioService->listarPorOcupacion(false));
    }

    /**
     * GET /api/horarios/ocupados
     * Replica: @GetMapping("/ocupados") → listarOcupados()
     */
    public function ocupados(): JsonResponse
    {
        return response()->json($this->horarioService->listarPorOcupacion(true));
    }

    /**
     * POST /api/horarios
     * Replica: @PostMapping → crearHorario()
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'espacioId' => 'required|integer',
            'bloqueId' => 'required|integer',
            'diaSemana' => 'required|string',
            'ocupado' => 'nullable|boolean',
        ]);

        try {
            $horario = $this->horarioService->crearHorario($validated);
            return response()->json([
                'exito' => true,
                'mensaje' => 'Horario creado correctamente',
                'horario' => $horario,
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'exito' => false,
                'mensaje' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * PUT /api/horarios/{id}
     * Replica: @PutMapping("/{id}") → actualizarHorario()
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'espacioId' => 'required|integer',
            'bloqueId' => 'required|integer',
            'diaSemana' => 'required|string',
            'ocupado' => 'nullable|boolean',
        ]);

        try {
            $horario = $this->horarioService->actualizarHorario($id, $validated);
            return response()->json([
                'exito' => true,
                'mensaje' => 'Horario actualizado correctamente',
                'horario' => $horario,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Horario no encontrado',
            ], 404);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'exito' => false,
                'mensaje' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * PATCH /api/horarios/{id}/ocupacion
     * Replica: @PatchMapping("/{id}/ocupacion") → actualizarOcupacion()
     */
    public function actualizarOcupacion(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'ocupado' => 'required|boolean',
        ]);

        try {
            $horario = $this->horarioService->actualizarOcupacion($id, $validated['ocupado']);
            return response()->json([
                'exito' => true,
                'mensaje' => 'Estado de ocupación actualizado',
                'horario' => $horario,
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Horario no encontrado',
            ], 404);
        }
    }

    /**
     * DELETE /api/horarios/{id}
     * Replica: @DeleteMapping("/{id}") → eliminarHorario()
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->horarioService->eliminarHorario($id);
            return response()->json([
                'exito' => true,
                'mensaje' => 'Horario eliminado correctamente',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'exito' => false,
                'mensaje' => $e->getMessage(),
            ], 404);
        }
    }
}
