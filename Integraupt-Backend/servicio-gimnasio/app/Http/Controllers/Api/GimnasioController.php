<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GimnasioService;
use Illuminate\Http\Request;
use InvalidArgumentException;
use LogicException;

class GimnasioController extends Controller
{
    public function __construct(private GimnasioService $service)
    {
    }

    public function registrarIngreso(Request $request)
    {
        $usuarioId = (int) $request->input('usuarioId', 0);
        
        if ($usuarioId < 1) {
            return response()->json(['error' => 'El parámetro usuarioId es obligatorio.'], 400);
        }

        try {
            $asistencia = $this->service->registrarIngreso($usuarioId);
            return response()->json($asistencia, 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (LogicException $e) {
            return response()->json(['error' => $e->getMessage()], 409);
        }
    }

    public function registrarSalida(Request $request)
    {
        $usuarioId = (int) $request->input('usuarioId', 0);
        
        if ($usuarioId < 1) {
            return response()->json(['error' => 'El parámetro usuarioId es obligatorio.'], 400);
        }

        try {
            $asistencia = $this->service->registrarSalida($usuarioId);
            return response()->json($asistencia, 200);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (LogicException $e) {
            return response()->json(['error' => $e->getMessage()], 409);
        }
    }

    public function estadoSesion(Request $request, int $usuarioId)
    {
        if ($usuarioId < 1) {
            return response()->json(['error' => 'El parámetro usuarioId es inválido.'], 400);
        }

        return response()->json($this->service->estadoSesion($usuarioId));
    }

    public function listarAsistencias(Request $request)
    {
        return response()->json($this->service->listarAsistencias());
    }
}
