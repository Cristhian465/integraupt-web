<?php

namespace App\Http\Controllers;

use App\Services\UsuarioBusquedaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsuarioBusquedaController extends Controller
{
    public function __construct(private UsuarioBusquedaService $usuarioBusquedaService)
    {
    }

    public function buscarUsuarios(Request $request): JsonResponse
    {
        try {
            $resultados = $this->usuarioBusquedaService->buscarUsuarios(
                $request->query('tipoUsuario'),
                $request->query('rol'),
                $request->query('query'),
                $request->query('facultadId') !== null ? (int) $request->query('facultadId') : null,
                $request->query('escuelaId') !== null ? (int) $request->query('escuelaId') : null,
                $request->query('escuelaContextoId') !== null ? (int) $request->query('escuelaContextoId') : null,
                $request->query('limit') !== null ? (int) $request->query('limit') : null,
            );

            return response()->json($resultados);
        } catch (\InvalidArgumentException $e) {
            return response()->json($e->getMessage(), 400);
        }
    }
}
