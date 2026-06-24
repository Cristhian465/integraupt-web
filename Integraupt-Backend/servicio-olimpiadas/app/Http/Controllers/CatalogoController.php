<?php

namespace App\Http\Controllers;

use App\Models\Escuela;
use App\Models\Estudiante;
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

    public function buscarEstudiantes(Request $request): JsonResponse
    {
        $busqueda = (string) $request->query('busqueda', '');

        $estudiantes = $this->catalogoService->buscarEstudiantes($busqueda)
            ->map(fn (Estudiante $estudiante) => [
                'usuarioId' => $estudiante->IdUsuario,
                'nombreCompleto' => trim(($estudiante->usuario?->Nombre ?? '') . ' ' . ($estudiante->usuario?->Apellido ?? '')),
                'codigo' => $estudiante->Codigo,
                'facultadId' => $estudiante->escuela?->IdFacultad,
                'facultadNombre' => $estudiante->escuela?->facultad?->Nombre,
            ])
            ->all();

        return response()->json($estudiantes);
    }
}
