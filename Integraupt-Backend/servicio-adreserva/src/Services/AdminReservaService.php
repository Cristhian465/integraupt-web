<?php

namespace App\Services;

use App\Core\HttpException;
use App\DTO\AdminGestionReservaRequest;
use App\DTO\AdminReservaCardDto;
use App\DTO\AdminReservaFilter;
use App\DTO\AdminReservaFiltersResponse;
use App\DTO\AdminReservaListResponse;
use App\DTO\AdminReservaSummaryDto;
use App\DTO\EscuelaOptionDto;
use App\DTO\SimpleOptionDto;
use App\Models\ReservaGestion;
use App\Repositories\AdministrativoRepository;
use App\Repositories\EscuelaRepository;
use App\Repositories\EspacioRepository;
use App\Repositories\FacultadRepository;
use App\Repositories\ReservaGestionRepository;
use App\Repositories\ReservaRepository;

class AdminReservaService {
    private ReservaRepository $reservaRepository;
    private ReservaGestionRepository $reservaGestionRepository;
    private FacultadRepository $facultadRepository;
    private EscuelaRepository $escuelaRepository;
    private EspacioRepository $espacioRepository;
    private AdministrativoRepository $administrativoRepository;

    public function __construct() {
        $this->reservaRepository = new ReservaRepository();
        $this->reservaGestionRepository = new ReservaGestionRepository();
        $this->facultadRepository = new FacultadRepository();
        $this->escuelaRepository = new EscuelaRepository();
        $this->espacioRepository = new EspacioRepository();
        $this->administrativoRepository = new AdministrativoRepository();
    }

    public function obtenerReservas(AdminReservaFilter $filter): AdminReservaListResponse {
        $estado = $filter->getEstado() !== null ? $this->normalizarEstado($filter->getEstado()) : null;
        $tipoEspacio = $filter->getTipoEspacio() !== null ? $this->normalizarTipo($filter->getTipoEspacio()) : null;
        $facultadId = $filter->getFacultadId();
        $escuelaId = $filter->getEscuelaId();
        $fecha = $filter->getFechaReserva();
        $search = $filter->getSearch() !== null ? trim($filter->getSearch()) : null;

        if ($this->esSupervisor($filter->getRol())) {
            $usuarioId = $filter->getUsuarioId();
            if ($usuarioId === null) {
                throw new HttpException("Se requiere el identificador del usuario supervisor.", 400);
            }
            $assignment = $this->obtenerAsignacionSupervisor($usuarioId);
            $facultadId = $assignment['facultadId'];
            $escuelaId = $assignment['escuelaId'];
        }

        $reservas = $this->reservaRepository->buscarReservasParaAdmin(
            $estado,
            $tipoEspacio,
            $facultadId,
            $escuelaId,
            $fecha,
            $search
        );

        $datosResumen = $this->reservaRepository->obtenerResumenEstados(
            $tipoEspacio,
            $facultadId,
            $escuelaId,
            $fecha,
            $search
        );

        $resumen = $this->construirResumen($datosResumen);

        return new AdminReservaListResponse($reservas, $resumen);
    }

    public function obtenerFiltros(?int $usuarioId, ?string $rol): AdminReservaFiltersResponse {
        $tipos = $this->espacioRepository->obtenerTiposDeEspacio();

        if ($this->esSupervisor($rol)) {
            if ($usuarioId === null) {
                throw new HttpException("Se requiere el identificador del usuario supervisor.", 400);
            }
            $assignment = $this->obtenerAsignacionSupervisor($usuarioId);

            $facultades = [
                new SimpleOptionDto($assignment['facultadId'], $assignment['facultadNombre'])
            ];

            $escuelas = [
                new EscuelaOptionDto($assignment['escuelaId'], $assignment['escuelaNombre'], $assignment['facultadId'])
            ];

            return new AdminReservaFiltersResponse($tipos, $facultades, $escuelas);
        }

        // Obtener y ordenar facultades alfabéticamente (insensible a mayúsculas/minúsculas)
        $facultadesDb = $this->facultadRepository->findAll();
        usort($facultadesDb, function ($a, $b) {
            return strcasecmp($a->nombre, $b->nombre);
        });
        $facultades = array_map(function ($facultad) {
            return new SimpleOptionDto($facultad->id, $facultad->nombre);
        }, $facultadesDb);

        // Obtener y ordenar escuelas alfabéticamente
        $escuelasDb = $this->escuelaRepository->findAll();
        usort($escuelasDb, function ($a, $b) {
            return strcasecmp($a->nombre, $b->nombre);
        });
        $escuelas = array_map(function ($escuela) {
            return new EscuelaOptionDto($escuela->id, $escuela->nombre, $escuela->facultadId);
        }, $escuelasDb);

        return new AdminReservaFiltersResponse($tipos, $facultades, $escuelas);
    }

    public function gestionarReserva(int $reservaId, AdminGestionReservaRequest $request): AdminReservaCardDto {
        $reserva = $this->reservaRepository->findById($reservaId);
        if ($reserva === null) {
            throw new HttpException("La reserva solicitada no existe", 404);
        }

        $accion = $request->accion;
        if ($accion === null || trim($accion) === '') {
            throw new HttpException("La acción de gestión no puede ser nula", 400);
        }

        $accionSanitizada = strtolower(trim($accion));
        if ($accionSanitizada === 'aprobar') {
            $accionBd = "Aprobar";
            $estadoResultado = "Aprobada";
        } elseif ($accionSanitizada === 'rechazar') {
            $accionBd = "Rechazar";
            $estadoResultado = "Rechazada";
        } else {
            throw new HttpException("Acción de gestión desconocida: " . $accion, 400);
        }

        if ($reserva->estado === $estadoResultado) {
            throw new HttpException("La reserva ya se encuentra en el estado solicitado", 409);
        }

        // Actualizar el estado de la reserva
        $reserva->estado = $estadoResultado;
        $this->reservaRepository->save($reserva);

        // Guardar registro de la gestión
        $gestion = new ReservaGestion(
            null,
            $reserva->id,
            $request->usuarioGestionId,
            null, // Usará por defecto date('Y-m-d H:i:s') en su constructor
            $accionBd,
            trim($request->motivo),
            $request->comentarios !== null ? trim($request->comentarios) : null
        );
        $this->reservaGestionRepository->save($gestion);

        // Obtener la reserva actualizada y mapeada
        $updatedCard = $this->reservaRepository->buscarReservaPorId($reserva->id);
        if ($updatedCard === null) {
            throw new HttpException("No fue posible obtener la información actualizada de la reserva", 500);
        }

        return $updatedCard;
    }

    private function construirResumen(array $datos): AdminReservaSummaryDto {
        if (empty($datos)) {
            return AdminReservaSummaryDto::empty();
        }

        return new AdminReservaSummaryDto(
            $datos['Pendiente'] ?? 0,
            $datos['Aprobada'] ?? 0,
            $datos['Rechazada'] ?? 0,
            $datos['Cancelada'] ?? 0
        );
    }

    private function esSupervisor(?string $rol): bool {
        return $rol !== null && strtolower(trim($rol)) === 'supervisor';
    }

    private function obtenerAsignacionSupervisor(int $usuarioId): array {
        $administrativo = $this->administrativoRepository->findByUsuarioId($usuarioId);
        if ($administrativo === null) {
            throw new HttpException("No se encontró un perfil administrativo para el usuario especificado.", 403);
        }

        $escuelaAsignadaId = $administrativo->escuelaId;
        if ($escuelaAsignadaId === null) {
            throw new HttpException("El supervisor no tiene una escuela asignada.", 403);
        }

        $escuela = $this->escuelaRepository->findById($escuelaAsignadaId);
        if ($escuela === null) {
            throw new HttpException("La escuela asignada al supervisor no existe.", 404);
        }

        $facultad = $this->facultadRepository->findById($escuela->facultadId);
        if ($facultad === null) {
            throw new HttpException("La facultad asociada a la escuela del supervisor no existe.", 404);
        }

        return [
            'escuelaId' => $escuela->id,
            'escuelaNombre' => $escuela->nombre,
            'facultadId' => $facultad->id,
            'facultadNombre' => $facultad->nombre
        ];
    }

    private function normalizarEstado(string $estado): string {
        $clean = strtolower(trim($estado));
        return match ($clean) {
            'pendiente' => 'Pendiente',
            'aprobada' => 'Aprobada',
            'rechazada' => 'Rechazada',
            'cancelada' => 'Cancelada',
            default => throw new HttpException("Estado de reserva desconocido: " . $estado, 400)
        };
    }

    private function normalizarTipo(string $tipo): ?string {
        $sanitized = trim($tipo);
        if ($sanitized === '') {
            return null;
        }
        // Capitalización de la primera letra (ej. "laboratorio" -> "Laboratorio")
        return mb_strtoupper(mb_substr($sanitized, 0, 1)) . mb_strtolower(mb_substr($sanitized, 1));
    }
}
