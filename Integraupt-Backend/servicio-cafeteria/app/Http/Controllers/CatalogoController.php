<?php

namespace App\Http\Controllers;

use App\Models\Administrativo;
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
            ])
            ->all();

        return response()->json($escuelas);
    }

    public function buscarAdministrativos(Request $request): JsonResponse
    {
        $busqueda = (string) $request->query('busqueda', '');

        $administrativos = $this->catalogoService->buscarAdministrativos($busqueda)
            ->map(fn (Administrativo $admin) => [
                'usuarioId' => $admin->IdUsuario,
                'nombreCompleto' => trim(($admin->usuario?->Nombre ?? '') . ' ' . ($admin->usuario?->Apellido ?? '')),
            ])
            ->all();

        return response()->json($administrativos);
    }

    public function miFacultad(Request $request): JsonResponse
    {
        $usuarioId = (int) $request->query('usuarioId');
        $facultadId = $this->catalogoService->resolverFacultadDeUsuario($usuarioId);

        return response()->json(['facultadId' => $facultadId]);
    }
}
