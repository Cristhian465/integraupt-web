<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\SancionRequest;
use App\Services\SancionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use LogicException;

class SancionController extends Controller
{
    private $sancionService;

    public function __construct(SancionService $sancionService)
    {
        $this->sancionService = $sancionService;
    }

    public function registrar(SancionRequest $request)
    {
        try {
            $response = $this->sancionService->registrarSancion($request->validated());
            return response()->json($response, 201);
        } catch (\Exception $e) {
            return $this->manejarExcepcion($e);
        }
    }

    public function obtenerTodas(Request $request)
    {
        try {
            $response = $this->sancionService->obtenerTodas(
                $request->query('rol'),
                $request->query('facultadId'),
                $request->query('escuelaId'),
                $request->query('escuelaContextoId')
            );
            return response()->json($response);
        } catch (\Exception $e) {
            return $this->manejarExcepcion($e);
        }
    }

    public function obtenerActivas(Request $request)
    {
        try {
            $response = $this->sancionService->obtenerActivas(
                $request->query('rol'),
                $request->query('facultadId'),
                $request->query('escuelaId'),
                $request->query('escuelaContextoId')
            );
            return response()->json($response);
        } catch (\Exception $e) {
            return $this->manejarExcepcion($e);
        }
    }

    public function levantar($id)
    {
        try {
            $response = $this->sancionService->levantarSancion($id);
            return response()->json($response);
        } catch (\Exception $e) {
            return $this->manejarExcepcion($e);
        }
    }

    public function verificar(Request $request)
    {
        try {
            $response = $this->sancionService->verificarUsuarioSancionado(
                $request->query('usuarioId'),
                $request->query('tipoUsuario')
            );
            return response()->json($response);
        } catch (\Exception $e) {
            return $this->manejarExcepcion($e);
        }
    }

    public function tieneSancionActiva(Request $request)
    {
        try {
            $response = $this->sancionService->tieneSancionActiva(
                $request->query('usuarioId'),
                $request->query('tipoUsuario')
            );
            return response()->json($response);
        } catch (\Exception $e) {
            return $this->manejarExcepcion($e);
        }
    }

    private function manejarExcepcion(\Exception $e)
    {
        if ($e instanceof InvalidArgumentException || $e instanceof ModelNotFoundException) {
            return response()->json($e->getMessage(), 404);
        }
        if ($e instanceof LogicException) {
            return response()->json($e->getMessage(), 409);
        }
        return response()->json($e->getMessage(), 500);
    }
}
