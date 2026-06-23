<?php

namespace App\Services;

use App\Models\Incidencia;
use App\Models\Reserva;
use App\Models\BloqueHorario;
use App\Models\Espacio;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IncidenciaService
{
    public function registrarIncidencia($request)
    {
        $reserva = Reserva::findOrFail($request['reservaId']);
        $bloque = BloqueHorario::findOrFail($reserva->bloque_id);

        $ventana = $this->calcularVentanaDisponibilidad(
            $reserva->fecha_reserva,
            $bloque->hora_inicio,
            $bloque->hora_fin
        );

        $ahora = Carbon::now();

        if (!$this->estaDentroDeVentana($ventana, $ahora)) {
            throw new \Exception("El formulario de incidencias ya no está disponible para esta reserva.");
        }

        $incidencia = Incidencia::create([
            'reserva_id' => $reserva->id,
            'descripcion' => $request['descripcion']
        ]);

        return $incidencia;
    }

    public function listarPorReserva($reservaId)
    {
        return Incidencia::where('reserva_id', $reservaId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function listarIncidenciasParaGestion(
        $rolValor,
        $facultadId,
        $escuelaId,
        $escuelaContextoId,
        $espacioId,
        $search
    ) {
        $query = DB::table('incidencias as i')
            ->join('reserva as r', 'r.id', '=', 'i.reserva_id')
            ->join('usuario as u', 'u.id', '=', 'r.usuario_id')
            ->join('espacio as e', 'e.id', '=', 'r.espacio_id')
            ->join('escuela as es', 'es.id', '=', 'u.escuela_id')
            ->join('facultad as f', 'f.id', '=', 'es.facultad_id')
            ->select(
                'i.id',
                'r.id as reservaId',
                'u.id as usuarioId',
                'u.nombre as usuarioNombre',
                'u.apellido as usuarioApellido',
                'u.documento as usuarioDocumento',
                'e.id as espacioId',
                'e.codigo as espacioCodigo',
                'e.nombre as espacioNombre',
                'es.id as escuelaId',
                'es.nombre as escuelaNombre',
                'f.id as facultadId',
                'f.nombre as facultadNombre',
                'i.descripcion',
                'i.created_at as fechaReporte'
            );

        if ($facultadId) {
            $query->where('f.id', $facultadId);
        }

        if ($escuelaId) {
            $query->where('es.id', $escuelaId);
        }

        if ($espacioId) {
            $query->where('e.id', $espacioId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('u.nombre', 'like', "%$search%")
                    ->orWhere('u.apellido', 'like', "%$search%")
                    ->orWhere('i.descripcion', 'like', "%$search%");
            });
        }

        return $query->get()->map(function ($row) {
            return [
                'id' => $row->id,
                'reservaId' => $row->reservaId,
                'usuarioId' => $row->usuarioId,
                'usuarioNombre' => trim($row->usuarioNombre . ' ' . $row->usuarioApellido),
                'usuarioDocumento' => $row->usuarioDocumento,
                'espacioId' => $row->espacioId,
                'espacioCodigo' => $row->espacioCodigo,
                'espacioNombre' => $row->espacioNombre,
                'escuelaId' => $row->escuelaId,
                'escuelaNombre' => $row->escuelaNombre,
                'facultadId' => $row->facultadId,
                'facultadNombre' => $row->facultadNombre,
                'descripcion' => $row->descripcion,
                'fechaReporte' => $row->fechaReporte,
            ];
        });
    }

    public function verificarDisponibilidad($reservaId)
    {
        $reserva = Reserva::findOrFail($reservaId);
        $bloque = BloqueHorario::findOrFail($reserva->bloque_id);

        $ventana = $this->calcularVentanaDisponibilidad(
            $reserva->fecha_reserva,
            $bloque->hora_inicio,
            $bloque->hora_fin
        );

        $ahora = Carbon::now();

        return [
            'reservaId' => $reservaId,
            'disponible' => $this->estaDentroDeVentana($ventana, $ahora),
            'inicio' => $ventana['inicio'],
            'fin' => $ventana['fin']
        ];
    }

    public function listarReservasParaUsuario($usuarioId)
    {
        $reservas = Reserva::where('usuario_id', $usuarioId)
            ->orderByDesc('fecha_reserva')
            ->limit(25)
            ->get();

        return $reservas->map(function ($reserva) {

            $bloque = BloqueHorario::find($reserva->bloque_id);
            if (!$bloque) return null;

            $ventana = $this->calcularVentanaDisponibilidad(
                $reserva->fecha_reserva,
                $bloque->hora_inicio,
                $bloque->hora_fin
            );

            $ahora = Carbon::now();

            if (!$this->estaDentroDeVentana($ventana, $ahora)) {
                return null;
            }

            $espacio = Espacio::find($reserva->espacio_id);

            return [
                'reservaId' => $reserva->id,
                'espacio' => $espacio,
                'habilitado' => true,
                'inicio' => $ventana['inicio'],
                'fin' => $ventana['fin']
            ];
        })->filter();
    }

    private function calcularVentanaDisponibilidad($fecha, $inicio, $fin)
    {
        $inicioReserva = Carbon::parse($fecha . ' ' . $inicio);
        $finReserva = Carbon::parse($fecha . ' ' . $fin);

        if ($finReserva->lte($inicioReserva)) {
            $finReserva->addDay();
        }

        $finDisponibilidad = $finReserva->copy()->addHours(24);

        return [
            'inicio' => $inicioReserva,
            'fin' => $finDisponibilidad
        ];
    }

    private function estaDentroDeVentana($ventana, $ahora)
    {
        return $ahora->between($ventana['inicio'], $ventana['fin']);
    }
}