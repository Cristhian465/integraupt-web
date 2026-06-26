<?php

namespace App\Services;

use App\Models\Evento;
use App\Models\EventoInscripcion;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EventoService
{
    private const ESTADOS_VALIDOS = [
        Evento::ESTADO_BORRADOR,
        Evento::ESTADO_PUBLICADO,
        Evento::ESTADO_EN_CURSO,
        Evento::ESTADO_FINALIZADO,
        Evento::ESTADO_CANCELADO,
    ];

    private const CONTEO_OCUPADOS = [
        EventoInscripcion::ESTADO_INSCRITO,
        EventoInscripcion::ESTADO_ASISTIO,
    ];

    public function listar(array $filtros): LengthAwarePaginator
    {
        $query = Evento::with(['facultad', 'escuela', 'espacio', 'responsable'])
            ->withCount(['inscripciones as inscritos_count' => fn ($q) => $q->whereIn('Estado', self::CONTEO_OCUPADOS)]);

        if (! empty($filtros['facultadId'])) {
            $query->where('IdFacultad', $filtros['facultadId']);
        }

        if (! empty($filtros['escuelaId'])) {
            $query->where('IdEscuela', $filtros['escuelaId']);
        }

        if (! empty($filtros['tipoEvento'])) {
            $query->where('TipoEvento', $filtros['tipoEvento']);
        }

        if (! empty($filtros['estado'])) {
            $query->where('Estado', $filtros['estado']);
        }

        if (! empty($filtros['desde'])) {
            $query->where('FechaFin', '>=', $filtros['desde']);
        }

        if (! empty($filtros['hasta'])) {
            $query->where('FechaInicio', '<=', $filtros['hasta']);
        }

        $paginador = $query->orderByDesc('FechaInicio')->paginate($filtros['porPagina'] ?? 15);

        $paginador->setCollection(
            $paginador->getCollection()->map(fn (Evento $evento) => $this->sincronizarEstado($evento))
        );

        return $paginador;
    }

    public function encontrar(int $id): Evento
    {
        $evento = Evento::with(['facultad', 'escuela', 'espacio', 'responsable'])
            ->withCount(['inscripciones as inscritos_count' => fn ($q) => $q->whereIn('Estado', self::CONTEO_OCUPADOS)])
            ->findOrFail($id);

        return $this->sincronizarEstado($evento);
    }

    /**
     * Corrige perezosamente el Estado del evento segun la fecha actual
     * (no hay scheduler configurado en el contenedor, asi que esto se
     * evalua cada vez que el evento se lista o se consulta).
     */
    private function sincronizarEstado(Evento $evento): Evento
    {
        $ahora = now();
        $estadosActivos = [Evento::ESTADO_PUBLICADO, Evento::ESTADO_EN_CURSO];

        if (! in_array($evento->Estado, $estadosActivos, true)) {
            return $evento;
        }

        if ($ahora->gte($evento->FechaFin)) {
            $evento->Estado = Evento::ESTADO_FINALIZADO;
            $evento->save();

            EventoInscripcion::where('IdEvento', $evento->IdEvento)
                ->where('Estado', EventoInscripcion::ESTADO_INSCRITO)
                ->update(['Estado' => EventoInscripcion::ESTADO_NO_ASISTIO]);

            return $evento;
        }

        if ($evento->Estado === Evento::ESTADO_PUBLICADO && $ahora->gte($evento->FechaInicio)) {
            $evento->Estado = Evento::ESTADO_EN_CURSO;
            $evento->save();
        }

        return $evento;
    }

    public function crear(array $datos): Evento
    {
        $this->validarAlcance($datos);
        $this->validarDisponibilidadEspacio($datos);

        $datos['Estado'] = Evento::ESTADO_BORRADOR;

        $evento = Evento::create($datos);

        return $evento->refresh();
    }

    public function actualizar(int $id, array $datos): Evento
    {
        $evento = Evento::findOrFail($id);
        $this->validarAlcance($datos);
        $this->validarDisponibilidadEspacio($datos, $id);

        $evento->update($datos);

        return $evento->refresh();
    }

    public function cambiarEstado(int $id, string $estado): Evento
    {
        if (! in_array($estado, self::ESTADOS_VALIDOS, true)) {
            throw new InvalidArgumentException("Estado de evento invalido: {$estado}");
        }

        $evento = Evento::findOrFail($id);
        $evento->update(['Estado' => $estado]);

        return $evento->refresh();
    }

    private function validarAlcance(array $datos): void
    {
        $alcance = $datos['Alcance'] ?? null;

        if ($alcance === Evento::ALCANCE_ESCUELA && empty($datos['IdEscuela'])) {
            throw new InvalidArgumentException('Debe indicar la escuela cuando el alcance del evento es por escuela.');
        }
    }

    private function validarDisponibilidadEspacio(array $datos, ?int $idEventoActual = null): void
    {
        if (empty($datos['IdEspacio'])) {
            return;
        }

        $solapados = Evento::where('IdEspacio', $datos['IdEspacio'])
            ->where('Estado', '!=', Evento::ESTADO_CANCELADO)
            ->when($idEventoActual, fn ($q) => $q->where('IdEvento', '!=', $idEventoActual))
            ->where('FechaInicio', '<', $datos['FechaFin'])
            ->where('FechaFin', '>', $datos['FechaInicio'])
            ->exists();

        if ($solapados) {
            throw new InvalidArgumentException('El espacio seleccionado ya tiene un evento programado en ese horario.');
        }
    }

    public function reporteAsistencia(int $id): array
    {
        $evento = Evento::findOrFail($id);

        $conteo = DB::table('evento_inscripcion')
            ->where('IdEvento', $id)
            ->selectRaw('Estado, count(*) as total')
            ->groupBy('Estado')
            ->pluck('total', 'Estado');

        return [
            'evento' => $evento->Titulo,
            'aforoMaximo' => $evento->AforoMaximo,
            'inscritos' => (int) ($conteo['inscrito'] ?? 0) + (int) ($conteo['asistio'] ?? 0),
            'asistieron' => (int) ($conteo['asistio'] ?? 0),
            'noAsistieron' => (int) ($conteo['no_asistio'] ?? 0),
            'cancelados' => (int) ($conteo['cancelado'] ?? 0),
        ];
    }
}
