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
        $facultades = $this->catalogoService->obtenerFacultades()
            ->map(fn (Facultad $facultad) => [
                'id' => $facultad->IdFacultad,
                'nombre' => $facultad->Nombre,
                'abreviatura' => $facultad->Abreviatura,
            ])
            ->all();

        return response()->json($facultades);
    }

    public function listarEscuelas(Request $request): JsonResponse
    {
        $facultadId = $request->query('facultadId');
        $facultadId = $facultadId !== null && $facultadId !== '' ? (int) $facultadId : null;

        $escuelas = $this->catalogoService->obtenerEscuelas($facultadId)
            ->map(fn (Escuela $escuela) => [
                'id' => $escuela->IdEscuela,
                'nombre' => $escuela->Nombre,
                'facultadId' => $escuela->IdFacultad,
                'facultadNombre' => $escuela->facultad?->Nombre,
            ])
            ->all();

        return response()->json($escuelas);
    }
}
