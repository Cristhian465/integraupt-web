<?php

namespace App\Http\Controllers;

use App\Services\EspacioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FormularioReservaController extends Controller
{
    public function __construct(private EspacioService $espacioService)
    {
    }

    public function mostrarFormulario(Request $request): JsonResponse
    {
        $espacioId = $request->query('espacioId');
        $espacioId = $espacioId !== null && $espacioId !== '' ? (int) $espacioId : null;

        $escuelaId = $request->query('escuelaId');
        $escuelaId = $escuelaId !== null && $escuelaId !== '' ? (int) $escuelaId : null;

        $espaciosDisponibles = $this->espacioService->listarActivosPorEscuela($escuelaId);

        $espacioSeleccionado = null;
        if ($espacioId !== null) {
            $espacioSeleccionado = $this->espacioService->buscarPorId($espacioId);
            if ($espacioSeleccionado !== null && !$espaciosDisponibles->contains('IdEspacio', $espacioId)) {
                $espaciosDisponibles->prepend($espacioSeleccionado);
            }
        }

        return response()->json([
            'espacios' => $espaciosDisponibles,
            'espacioIdSeleccionado' => $espacioId,
            'espacioSeleccionDescripcion' => $espacioSeleccionado !== null
                ? $espacioSeleccionado->Codigo . ' - ' . $espacioSeleccionado->Nombre
                : null,
        ]);
    }
}
