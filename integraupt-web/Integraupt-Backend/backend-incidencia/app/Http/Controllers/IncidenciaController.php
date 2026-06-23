<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\IncidenciaRequest;
use App\Services\IncidenciaService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class IncidenciaController extends Controller
{
    private $service;

    public function __construct(IncidenciaService $service)
    {
        $this->service = $service;
    }

    public function registrarIncidencia(IncidenciaRequest $request)
    {
        try {
            $incidencia = $this->service->registrarIncidencia($request->validated());
            return response()->json($incidencia, 201);

        } catch (\InvalidArgumentException | ModelNotFoundException $e) {
            $message = $e instanceof ModelNotFoundException ? "No se encontró la reserva indicada." : $e->getMessage();
            return response()->json($message, 404);

        } catch (\LogicException $e) {
            return response()->json($e->getMessage(), 409);
            
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    public function listarParaGestion(Request $request)
    {
        return response()->json(
            $this->service->listarIncidenciasParaGestion(
                $request->rol,
                $request->facultadId,
                $request->escuelaId,
                $request->escuelaContextoId,
                $request->espacioId,
                $request->search
            )
        );
    }

    public function listarPorReserva($reservaId)
    {
        return response()->json(
            $this->service->listarPorReserva($reservaId)
        );
    }

    public function verificarDisponibilidad($reservaId)
    {
        return response()->json(
            $this->service->verificarDisponibilidad($reservaId)
        );
    }

    public function listarReservasPorUsuario($usuarioId)
    {
        return response()->json(
            $this->service->listarReservasParaUsuario($usuarioId)
        );
    }
}