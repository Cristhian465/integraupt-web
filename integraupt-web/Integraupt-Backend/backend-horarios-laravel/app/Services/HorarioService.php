<?php

namespace App\Services;

use App\Enums\DiaSemana;
use App\Models\BloqueHorario;
use App\Models\Espacio;
use App\Models\Horario;
use Carbon\Carbon;

/**
 * Servicio de negocio para Horarios.
 * Replica: com.horarios.service.HorarioService
 */
class HorarioService
{
    /**
     * Listar todos los horarios.
     * Replica: listarTodos()
     */
    public function listarTodos(): array
    {
        $horarios = Horario::with(['espacioRelation', 'bloqueRelation'])->get();
        return $horarios->map(fn($h) => $this->mapToResponse($h))->toArray();
    }

    /**
     * Buscar horario por ID.
     * Replica: buscarPorId(Integer id)
     */
    public function buscarPorId(int $id): ?array
    {
        $horario = Horario::with(['espacioRelation', 'bloqueRelation'])->find($id);
        return $horario ? $this->mapToResponse($horario) : null;
    }

    /**
     * Listar horarios por espacio, ordenados por bloque.orden ASC, diaSemana ASC.
     * Replica: listarPorEspacio(Integer espacioId)
     */
    public function listarPorEspacio(int $espacioId): array
    {
        $horarios = Horario::with(['espacioRelation', 'bloqueRelation'])
            ->where('horarios.espacio', $espacioId)
            ->join('bloqueshorarios', 'horarios.bloque', '=', 'bloqueshorarios.IdBloque')
            ->orderBy('bloqueshorarios.Orden', 'asc')
            ->orderBy('horarios.diaSemana', 'asc')
            ->select('horarios.*')
            ->get();

        return $horarios->map(fn($h) => $this->mapToResponse($h))->toArray();
    }

    /**
     * Listar horarios por día de la semana, ordenados por bloque.orden ASC.
     * Replica: listarPorDia(String diaSemana)
     */
    public function listarPorDia(string $diaSemana): array
    {
        $dia = DiaSemana::fromNombre($diaSemana);

        $horarios = Horario::with(['espacioRelation', 'bloqueRelation'])
            ->where('horarios.diaSemana', $dia->value)
            ->join('bloqueshorarios', 'horarios.bloque', '=', 'bloqueshorarios.IdBloque')
            ->orderBy('bloqueshorarios.Orden', 'asc')
            ->select('horarios.*')
            ->get();

        return $horarios->map(fn($h) => $this->mapToResponse($h))->toArray();
    }

    /**
     * Listar horarios por estado de ocupación.
     * Replica: listarPorOcupacion(boolean ocupado)
     */
    public function listarPorOcupacion(bool $ocupado): array
    {
        $horarios = Horario::with(['espacioRelation', 'bloqueRelation'])
            ->where('ocupado', $ocupado)
            ->get();

        return $horarios->map(fn($h) => $this->mapToResponse($h))->toArray();
    }

    /**
     * Obtener horario semanal completo por espacio.
     * Genera una matriz bloques × días con estado de ocupación.
     * Replica: obtenerHorarioSemanalPorEspacio(Integer espacioId)
     */
    public function obtenerHorarioSemanalPorEspacio(int $espacioId): array
    {
        // Obtener horarios del espacio
        $horarios = Horario::where('espacio', $espacioId)->get();

        // Indexar estados por bloqueId → diaSemana → ocupado
        $estadosPorBloque = [];
        foreach ($horarios as $horario) {
            $bloqueId = $horario->bloque;
            $dia = $horario->diaSemana;

            if ($bloqueId === null || $dia === null) {
                continue;
            }

            $estadosPorBloque[$bloqueId][$dia] = (bool) $horario->ocupado;
        }

        // Obtener todos los bloques ordenados
        $bloques = BloqueHorario::orderBy('Orden', 'asc')->get();
        $diasOrdenados = DiaSemana::ordered();

        return $bloques->map(function ($bloque) use ($estadosPorBloque, $diasOrdenados) {
            $estados = $estadosPorBloque[$bloque->IdBloque] ?? [];

            $dias = array_map(function ($dia) use ($estados) {
                return [
                    'diaSemana' => $dia->value,
                    'ocupado' => $estados[$dia->value] ?? false,
                ];
            }, $diasOrdenados);

            $nombre = !empty(trim($bloque->Nombre ?? ''))
                ? $bloque->Nombre
                : sprintf('Bloque %d', $bloque->IdBloque);

            $horaInicio = $bloque->HoraInicio
                ? Carbon::parse($bloque->HoraInicio)->format('H:i')
                : '--:--';

            $horaFin = $bloque->HoraFinal
                ? Carbon::parse($bloque->HoraFinal)->format('H:i')
                : '--:--';

            return [
                'bloqueId' => $bloque->IdBloque,
                'bloqueNombre' => $nombre,
                'horaInicio' => $horaInicio,
                'horaFin' => $horaFin,
                'dias' => $dias,
            ];
        })->toArray();
    }

    /**
     * Crear un nuevo horario.
     * Replica: crearHorario(HorarioRequest request)
     */
    public function crearHorario(array $data): array
    {
        $espacio = $this->obtenerEspacio($data['espacioId']);
        $bloque = BloqueHorario::findOrFail($data['bloqueId']);
        $dia = DiaSemana::fromNombre($data['diaSemana']);

        $horario = new Horario();
        $horario->espacio = $espacio->IdEspacio;
        $horario->bloque = $bloque->IdBloque;
        $horario->diaSemana = $dia->value;
        $horario->ocupado = !empty($data['ocupado']);
        $horario->save();

        $horario->load(['espacioRelation', 'bloqueRelation']);
        return $this->mapToResponse($horario);
    }

    /**
     * Actualizar un horario existente.
     * Replica: actualizarHorario(Integer id, HorarioRequest request)
     */
    public function actualizarHorario(int $id, array $data): array
    {
        $horario = Horario::findOrFail($id);

        $espacio = $this->obtenerEspacio($data['espacioId']);
        $bloque = BloqueHorario::findOrFail($data['bloqueId']);
        $dia = DiaSemana::fromNombre($data['diaSemana']);

        $horario->espacio = $espacio->IdEspacio;
        $horario->bloque = $bloque->IdBloque;
        $horario->diaSemana = $dia->value;

        if (isset($data['ocupado'])) {
            $horario->ocupado = $data['ocupado'];
        }

        $horario->save();
        $horario->load(['espacioRelation', 'bloqueRelation']);
        return $this->mapToResponse($horario);
    }

    /**
     * Eliminar un horario.
     * Replica: eliminarHorario(Integer id)
     */
    public function eliminarHorario(int $id): void
    {
        $horario = Horario::find($id);
        if (!$horario) {
            throw new \InvalidArgumentException('Horario no encontrado');
        }
        $horario->delete();
    }

    /**
     * Actualizar solo el estado de ocupación.
     * Replica: actualizarOcupacion(Integer id, ActualizarOcupacionRequest request)
     */
    public function actualizarOcupacion(int $id, bool $ocupado): array
    {
        $horario = Horario::findOrFail($id);
        $horario->ocupado = $ocupado;
        $horario->save();

        $horario->load(['espacioRelation', 'bloqueRelation']);
        return $this->mapToResponse($horario);
    }

    /**
     * Mapear un Horario Eloquent a la estructura de respuesta JSON.
     * Replica: HorarioMapper.toResponse(Horario horario)
     */
    private function mapToResponse(Horario $horario): array
    {
        $espacio = $horario->espacioRelation;
        $bloque = $horario->bloqueRelation;

        $horaInicio = $bloque && $bloque->HoraInicio
            ? Carbon::parse($bloque->HoraInicio)->format('H:i')
            : null;

        $horaFin = $bloque && $bloque->HoraFinal
            ? Carbon::parse($bloque->HoraFinal)->format('H:i')
            : null;

        return [
            'id' => $horario->IdHorario,
            'espacioId' => $espacio?->IdEspacio,
            'espacioNombre' => $espacio?->Nombre,
            'espacioCodigo' => $espacio?->Codigo,
            'bloqueId' => $bloque?->IdBloque,
            'bloqueNombre' => $bloque?->Nombre,
            'horaInicio' => $horaInicio,
            'horaFin' => $horaFin,
            'diaSemana' => $horario->diaSemana,
            'ocupado' => (bool) $horario->ocupado,
        ];
    }

    /**
     * Obtener espacio o lanzar excepción.
     * Replica: obtenerEspacio(Integer espacioId)
     */
    private function obtenerEspacio(int $espacioId): Espacio
    {
        $espacio = Espacio::find($espacioId);
        if (!$espacio) {
            throw new \InvalidArgumentException('Espacio no encontrado');
        }
        return $espacio;
    }
}
