<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PsicologiaService;
use Illuminate\Http\Request;
use InvalidArgumentException;
use LogicException;

class PsicologiaController extends Controller
{
    public function __construct(private PsicologiaService $service)
    {
    }

    public function listarPsicologos()
    {
        return response()->json($this->service->listarPsicologos());
    }

    public function bloquesDisponibles(Request $request, int $psicologoId)
    {
        $fecha = $request->query('fecha');

        if (!$fecha) {
            return response()->json(['error' => 'El parámetro fecha es obligatorio.'], 400);
        }

        return response()->json($this->service->listarBloquesDisponibles($psicologoId, $fecha));
    }

    public function listarCitas(Request $request)
    {
        $usuarioId = (int) $request->query('usuarioId', 0);

        if ($usuarioId < 1) {
            return response()->json(['error' => 'El parámetro usuarioId es obligatorio.'], 400);
        }

        return response()->json($this->service->listarCitasPorUsuario($usuarioId));
    }

    public function registrarCita(Request $request)
    {
        try {
            $cita = $this->service->registrarCita($request->all());
            return response()->json($cita, 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (LogicException $e) {
            return response()->json(['error' => $e->getMessage()], 409);
        }
    }

    public function cancelarCita(Request $request, int $id)
    {
        $usuarioId = (int) $request->query('usuarioId', 0);

        try {
            $cita = $this->service->cancelarCita($id, $usuarioId);
            return response()->json($cita);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        } catch (LogicException $e) {
            return response()->json(['error' => $e->getMessage()], 409);
        }
    }
}
