<?php

namespace App\Http\Controllers;

use App\Models\BloqueHorario;
use App\Models\Curso;
use App\Services\EspacioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EspacioController extends Controller
{
    public function __construct(private EspacioService $espacioService)
    {
    }

    public function listarEspacios(Request $request): JsonResponse
    {
        $incluirInactivos = $request->boolean('incluirInactivos', false);
        $escuelaId = $request->query('escuelaId');
        $escuelaId = $escuelaId !== null && $escuelaId !== '' ? (int) $escuelaId : null;

        $espacios = $incluirInactivos
            ? $this->espacioService->listarTodosPorEscuela($escuelaId)
            : $this->espacioService->listarActivosPorEscuela($escuelaId);

        return response()->json($espacios);
    }

    public function listarCursosPorEspacio(int $espacioId): JsonResponse
    {
        $cursos = $this->espacioService->listarCursosActivosPorEspacio($espacioId)
            ->map(fn (Curso $curso) => [
                'id' => $curso->IdCurso,
                'nombre' => $curso->Nombre,
                'ciclo' => $curso->Ciclo,
            ])
            ->all();

        return response()->json($cursos);
    }

    public function listarBloquesPorEspacio(int $espacioId): JsonResponse
    {
        $bloques = $this->espacioService->listarBloquesPorEspacio($espacioId)
            ->map(fn (BloqueHorario $bloque) => [
                'id' => $bloque->IdBloque,
                'nombre' => $bloque->Nombre,
                'orden' => $bloque->Orden,
                'horaInicio' => $bloque->HoraInicio !== null ? substr((string) $bloque->HoraInicio, 0, 5) : null,
                'horaFinal' => $bloque->HoraFinal !== null ? substr((string) $bloque->HoraFinal, 0, 5) : null,
            ])
            ->all();

        return response()->json($bloques);
    }
}
