<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SancionService;

class SancionController extends Controller
{
    private $sancionService;

    public function __construct(SancionService $sancionService)
    {
        $this->sancionService = $sancionService;
    }

    // =========================
    // 🟢 REGISTRAR SANCION
    // =========================
    public function registrar(Request $request)
    {
        $data = $request->all();

        $resultado = $this->sancionService->registrarSancion($data);

        return response()->json($resultado, 201);
    }

    // =========================
    // 🟢 OBTENER TODAS
    // =========================
    public function obtenerTodas(Request $request)
    {
        $resultado = $this->sancionService->obtenerTodas(
            $request->rol,
            $request->facultadId,
            $request->escuelaId,
            $request->escuelaContextoId
        );

        return response()->json($resultado);
    }

    // =========================
    // 🟢 OBTENER ACTIVAS
    // =========================
    public function obtenerActivas(Request $request)
    {
        $resultado = $this->sancionService->obtenerActivas(
            $request->rol,
            $request->facultadId,
            $request->escuelaId,
            $request->escuelaContextoId
        );

        return response()->json($resultado);
    }

    // =========================
    // 🟢 LEVANTAR SANCION
    // =========================
    public function levantar($id)
    {
        $resultado = $this->sancionService->levantarSancion($id);

        return response()->json($resultado);
    }

    // =========================
    // 🟢 VERIFICACION
    // =========================
    public function verificar(Request $request)
    {
        $resultado = $this->sancionService->verificarUsuarioSancionado(
            $request->usuarioId,
            $request->tipoUsuario
        );

        return response()->json($resultado);
    }

    // =========================
    // 🟢 ESTADO BOOLEAN
    // =========================
    public function tieneSancionActiva(Request $request)
    {
        $resultado = $this->sancionService->tieneSancionActiva(
            $request->usuarioId,
            $request->tipoUsuario
        );

        return response()->json($resultado);
    }
}