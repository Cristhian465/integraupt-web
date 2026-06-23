<?php

namespace App\Http\Controllers;

use App\Models\Escuela;
use App\Models\Facultad;
use App\Services\CatalogoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    public function __construct(private CatalogoService $catalogoService)
    {
    }

    public function listarFacultades(): JsonResponse
    {
        $facultades = $this->catalogoService->listarFacultades()
            ->map(fn (Facultad $facultad) => $this->facultadResponse($facultad))
            ->all();

        return response()->json($facultades);
    }

    public function listarEscuelas(Request $request): JsonResponse
    {
        $facultadId = $request->query('facultadId');
        $facultadId = $facultadId !== null && $facultadId !== '' ? (int) $facultadId : null;

        $escuelas = $this->catalogoService->listarEscuelas($facultadId)
            ->map(fn (Escuela $escuela) => $this->escuelaResponse($escuela))
            ->all();

        return response()->json($escuelas);
    }

    private function facultadResponse(Facultad $facultad): array
    {
        return [
            'id' => $facultad->IdFacultad,
            'nombre' => $facultad->Nombre,
            'abreviatura' => $facultad->Abreviatura,
        ];
    }

    private function escuelaResponse(Escuela $escuela): array
    {
        return [
            'id' => $escuela->IdEscuela,
            'nombre' => $escuela->Nombre,
            'facultadId' => $escuela->IdFacultad,
            'facultadNombre' => $escuela->facultad?->Nombre,
        ];
    }
}
