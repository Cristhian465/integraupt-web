<?php

namespace App\Services;

use App\Models\Incidencia;
use App\Models\Reserva;
use App\Models\BloqueHorario;
use App\Models\Espacio;
use App\Repositories\IncidenciaRepository;
use Carbon\Carbon;
use InvalidArgumentException;
use LogicException;

class IncidenciaService
{
    private $repository;

    public function __construct(IncidenciaRepository $repository)
    {
        $this->repository = $repository;
    }

    public function registrarIncidencia(array $request)
    {
        $reserva = Reserva::findOrFail($request['reservaId']);
        $bloque = BloqueHorario::findOrFail($reserva->bloque);

        $ventana = $this->calcularVentanaDisponibilidad(
            $reserva->fechaReserva,
            $bloque->HoraInicio,
            $bloque->HoraFinal
        );

        $ahora = Carbon::now();

        if (!$this->estaDentroDeVentana($ventana, $ahora)) {
            throw new LogicException("El formulario de incidencias ya no está disponible para esta reserva.");
        }

        $incidencia = Incidencia::create([
            'Reserva' => $reserva->IdReserva,
            'Descripcion' => $request['descripcion'],
            'FechaReporte' => Carbon::now(),
        ]);

        return $this->mapearResponse($incidencia);
    }

    public function listarPorReserva($reservaId)
    {
        return $this->repository->findByReservaIdOrderByFechaReporteDesc($reservaId)
            ->map(function ($incidencia) {
                return $this->mapearResponse($incidencia);
            });
    }

    public function listarIncidenciasParaGestion(
        $rolValor,
        $facultadId,
        $escuelaId,
        $escuelaContextoId,
        $espacioId,
        $search
    ) {
        $rol = strtoupper(trim($rolValor ?? ''));
        $filtros = $this->construirFiltroContexto($rol, $facultadId, $escuelaId, $escuelaContextoId);

        $terminoBusqueda = ($search !== null && trim($search) !== '') ? trim($search) : null;

        $registros = $this->repository->buscarParaGestion(
            $filtros['facultadId'],
            $filtros['escuelaId'],
            $espacioId,
            $terminoBusqueda
        );

        return $registros->map(function ($fila) {
            return $this->mapearGestion($fila);
        });
    }

    public function verificarDisponibilidad($reservaId)
    {
        $reserva = Reserva::findOrFail($reservaId);
        $bloque = BloqueHorario::findOrFail($reserva->bloque);

        $ventana = $this->calcularVentanaDisponibilidad(
            $reserva->fechaReserva,
            $bloque->HoraInicio,
            $bloque->HoraFinal
        );

        $ahora = Carbon::now();
        $habilitado = $this->estaDentroDeVentana($ventana, $ahora);

        return [
            'reservaId' => $reservaId,
            'habilitado' => $habilitado,
            'habilitadoDesde' => $ventana['inicio']->format('Y-m-d\TH:i:s'),
            'habilitadoHasta' => $ventana['fin']->format('Y-m-d\TH:i:s'),
        ];
    }

    public function listarReservasParaUsuario($usuarioId)
    {
        $reservas = Reserva::where('usuario', $usuarioId)
            ->orderByDesc('fechaReserva')
            ->limit(25)
            ->get();

        if ($reservas->isEmpty()) {
            return [];
        }

        $bloqueIds = $reservas->pluck('bloque')->filter()->unique();
        $bloques = BloqueHorario::whereIn('IdBloque', $bloqueIds)->get()->keyBy('IdBloque');

        $espacioIds = $reservas->pluck('espacio')->filter()->unique();
        $espacios = Espacio::whereIn('IdEspacio', $espacioIds)->get()->keyBy('IdEspacio');

        $ahora = Carbon::now();

        $resultados = $reservas->map(function ($reserva) use ($bloques, $espacios, $ahora) {
            $bloque = $bloques->get($reserva->bloque);
            if (!$bloque) {
                return null;
            }

            $ventana = $this->calcularVentanaDisponibilidad(
                $reserva->fechaReserva,
                $bloque->HoraInicio,
                $bloque->HoraFinal
            );

            $habilitado = $this->estaDentroDeVentana($ventana, $ahora);
            $espacio = $espacios->get($reserva->espacio);
            $nombre = $espacio?->Nombre;
            $codigo = $espacio?->Codigo;

            return [
                'reservaId' => $reserva->IdReserva,
                'espacioId' => $espacio?->IdEspacio ?? $reserva->espacio,
                'espacioNombre' => $nombre !== null && trim($nombre) !== '' ? $nombre : "Espacio {$reserva->espacio}",
                'espacioCodigo' => $codigo !== null && trim((string) $codigo) !== '' ? $codigo : null,
                'fechaReserva' => Carbon::parse($reserva->fechaReserva)->format('Y-m-d'),
                'horaInicio' => substr((string) $bloque->HoraInicio, 0, 5),
                'horaFin' => substr((string) $bloque->HoraFinal, 0, 5),
                'estado' => $reserva->Estado,
                'habilitado' => $habilitado,
                'habilitadoDesde' => $ventana['inicio']->format('Y-m-d\TH:i:s'),
                'habilitadoHasta' => $ventana['fin']->format('Y-m-d\TH:i:s'),
            ];
        })->filter(function ($item) {
            return $item !== null && $item['habilitado'] === true;
        })->values();

        return $resultados->sortByDesc(function ($item) {
            return $item['habilitadoDesde'];
        })->values()->all();
    }

    private function calcularVentanaDisponibilidad($fecha, $inicio, $fin)
    {
        $fechaSolo = Carbon::parse($fecha)->format('Y-m-d');

        $inicioReserva = Carbon::parse($fechaSolo . ' ' . $inicio);
        $finReserva = Carbon::parse($fechaSolo . ' ' . $fin);

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
        return $ahora->betweenIncluded($ventana['inicio'], $ventana['fin']);
    }

    private function mapearResponse($incidencia)
    {
        return [
            'id' => $incidencia->IdIncidencia,
            'reservaId' => $incidencia->Reserva,
            'descripcion' => $incidencia->Descripcion,
            'fechaReporte' => $incidencia->FechaReporte ? $incidencia->FechaReporte->format('Y-m-d\TH:i:s') : null,
        ];
    }

    private function mapearGestion($fila)
    {
        $nombreCompleto = $this->combinarNombre($fila->usuarioNombre, $fila->usuarioApellido);
        return [
            'id' => $fila->id,
            'reservaId' => $fila->reservaId,
            'usuarioId' => $fila->usuarioId,
            'usuarioNombre' => $this->normalizar($nombreCompleto),
            'usuarioDocumento' => $this->normalizar($fila->usuarioDocumento),
            'espacioId' => $fila->espacioId,
            'espacioCodigo' => $this->normalizar($fila->espacioCodigo),
            'espacioNombre' => $this->normalizar($fila->espacioNombre),
            'escuelaId' => $fila->escuelaId,
            'escuelaNombre' => $this->normalizar($fila->escuelaNombre),
            'facultadId' => $fila->facultadId,
            'facultadNombre' => $this->normalizar($fila->facultadNombre),
            'descripcion' => $this->normalizar($fila->descripcion),
            'fechaReporte' => Carbon::parse($fila->fechaReporte)->format('Y-m-d\TH:i:s')
        ];
    }

    private function combinarNombre($nombre, $apellido)
    {
        $nombreValor = $this->normalizar($nombre);
        $apellidoValor = $this->normalizar($apellido);

        if ($nombreValor === null && $apellidoValor === null) {
            return null;
        }

        $nombreTexto = $nombreValor !== null ? $nombreValor : "";
        $apellidoTexto = $apellidoValor !== null ? $apellidoValor : "";
        $combinado = trim($nombreTexto . " " . $apellidoTexto);

        return $combinado === '' ? null : $combinado;
    }

    private function normalizar($valor)
    {
        if ($valor === null || trim($valor) === '') {
            return null;
        }
        $trimmed = trim($valor);
        return $trimmed === '' ? null : $trimmed;
    }

    private function construirFiltroContexto($rol, $facultadId, $escuelaId, $escuelaContextoId)
    {
        $facultadFiltro = $facultadId;
        $escuelaFiltro = $escuelaId;

        if ($rol === 'SUPERVISOR') {
            $escuelaRestriccion = $escuelaContextoId !== null ? $escuelaContextoId : $escuelaId;
            if ($escuelaRestriccion === null) {
                throw new InvalidArgumentException("Los supervisores deben tener una escuela asociada para revisar incidencias.");
            }
            $escuelaFiltro = $escuelaRestriccion;
            $facultadFiltro = null;
        }

        return [
            'facultadId' => $facultadFiltro,
            'escuelaId' => $escuelaFiltro
        ];
    }
}
