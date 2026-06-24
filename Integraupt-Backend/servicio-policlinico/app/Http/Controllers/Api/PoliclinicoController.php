<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PoliclinicoService;
use Illuminate\Http\Request;
use InvalidArgumentException;
use LogicException;

class PoliclinicoController extends Controller
{
    public function __construct(private PoliclinicoService $service)
    {
    }

    public function listarTiposAtencion()
    {
        return response()->json($this->service->listarTiposAtencion());
    }

    public function listarMedicos(Request $request)
    {
        $tipoAtencionId = (int) $request->query('tipoAtencionId', 0);

        if ($tipoAtencionId < 1) {
            return response()->json(['error' => 'El parámetro tipoAtencionId es obligatorio.'], 400);
        }

        return response()->json($this->service->listarMedicosPorTipoAtencion($tipoAtencionId));
    }

    public function bloquesDisponibles(Request $request, int $medicoId)
    {
        $fecha = $request->query('fecha');

        if (!$fecha) {
            return response()->json(['error' => 'El parámetro fecha es obligatorio.'], 400);
        }

        return response()->json($this->service->listarBloquesDisponibles($medicoId, $fecha));
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

    public function listarCitasAdmin(Request $request)
    {
        $filtros = $request->only(['estado', 'fecha', 'medicoId', 'tipoAtencionId']);

        return response()->json($this->service->listarCitasAdmin($filtros));
    }

    public function cambiarEstadoCita(Request $request, int $id)
    {
        $estado = (string) $request->input('estado', '');

        try {
            $cita = $this->service->cambiarEstadoCita($id, $estado);
            return response()->json($cita);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function listarTiposAtencionAdmin()
    {
        return response()->json($this->service->listarTiposAtencionAdmin());
    }

    public function crearTipoAtencion(Request $request)
    {
        try {
            $tipo = $this->service->crearTipoAtencion($request->all());
            return response()->json($tipo, 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function actualizarTipoAtencion(Request $request, int $id)
    {
        try {
            $tipo = $this->service->actualizarTipoAtencion($id, $request->all());
            return response()->json($tipo);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function listarMedicosAdmin()
    {
        return response()->json($this->service->listarMedicosAdmin());
    }

    public function crearMedico(Request $request)
    {
        try {
            $medico = $this->service->crearMedico($request->all());
            return response()->json($medico, 201);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function actualizarMedico(Request $request, int $id)
    {
        try {
            $medico = $this->service->actualizarMedico($id, $request->all());
            return response()->json($medico);
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}
