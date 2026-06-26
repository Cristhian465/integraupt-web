<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\Escuela;
use App\Models\Espacio;
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

    public function listarEspacios(Request $request): JsonResponse
    {
        $query = Espacio::with('escuela')->where('Estado', 1)->orderBy('Nombre');

        if ($request->filled('escuelaId')) {
            $query->where('Escuela', (int) $request->query('escuelaId'));
        }

        $espacios = $query->get()->map(fn (Espacio $espacio) => [
            'id' => $espacio->IdEspacio,
            'codigo' => $espacio->Codigo,
            'nombre' => $espacio->Nombre,
            'capacidad' => $espacio->Capacidad,
            'escuelaId' => $espacio->Escuela,
        ])->all();

        return response()->json($espacios);
    }

    public function buscarEstudiantes(Request $request): JsonResponse
    {
        $busqueda = (string) $request->query('busqueda', '');

        $estudiantes = $this->catalogoService->buscarEstudiantes($busqueda)
            ->map(fn (Estudiante $estudiante) => [
                'usuarioId' => $estudiante->IdUsuario,
                'nombreCompleto' => trim(($estudiante->usuario?->Nombre ?? '') . ' ' . ($estudiante->usuario?->Apellido ?? '')),
                'codigo' => $estudiante->Codigo,
                'escuelaId' => $estudiante->Escuela,
                'facultadId' => $estudiante->escuela?->IdFacultad,
            ])
            ->all();

        return response()->json($estudiantes);
    }

    public function buscarDocentes(Request $request): JsonResponse
    {
        $busqueda = (string) $request->query('busqueda', '');

        $docentes = $this->catalogoService->buscarDocentes($busqueda)
            ->map(fn (Docente $docente) => [
                'usuarioId' => $docente->IdUsuario,
                'nombreCompleto' => trim(($docente->usuario?->Nombre ?? '') . ' ' . ($docente->usuario?->Apellido ?? '')),
                'codigo' => $docente->CodigoDocente,
                'escuelaId' => $docente->Escuela,
                'facultadId' => $docente->escuela?->IdFacultad,
            ])
            ->all();

        return response()->json($docentes);
    }

    public function miFacultad(Request $request): JsonResponse
    {
        $usuarioId = (int) $request->query('usuarioId');
        $resultado = $this->catalogoService->resolverFacultadDeUsuario($usuarioId);

        if ($resultado === null) {
            return response()->json(['message' => 'Usuario no encontrado como estudiante o docente.'], 404);
        }

        return response()->json($resultado);
    }
}
