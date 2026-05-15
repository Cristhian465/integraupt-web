package com.AdReserva.service;

import com.AdReserva.dto.AdminGestionReservaRequest;
import com.AdReserva.dto.AdminReservaCardDto;
import com.AdReserva.dto.AdminReservaFilter;
import com.AdReserva.dto.AdminReservaFiltersResponse;
import com.AdReserva.dto.AdminReservaListResponse;
import com.AdReserva.dto.AdminReservaSummaryDto;
import com.AdReserva.dto.EscuelaOptionDto;
import com.AdReserva.dto.ReservaGestionAccion;
import com.AdReserva.dto.SimpleOptionDto;
import com.AdReserva.model.Administrativo;
import com.AdReserva.model.Facultad;
import com.AdReserva.model.Escuela;
import com.AdReserva.model.Reserva;
import com.AdReserva.model.ReservaGestion;
import com.AdReserva.repository.AdministrativoRepository;
import com.AdReserva.repository.EscuelaRepository;
import com.AdReserva.repository.EspacioRepository;
import com.AdReserva.repository.FacultadRepository;
import com.AdReserva.repository.ReservaGestionRepository;
import com.AdReserva.repository.ReservaRepository;
import com.AdReserva.repository.projection.AdminReservaEstadoProjection;
import com.AdReserva.repository.projection.AdminReservaProjection;
import com.AdReserva.service.mapper.AdminReservaMapper;
import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.server.ResponseStatusException;

import java.time.LocalDate;
import java.util.Comparator;
import java.util.List;
import java.util.Locale;
import java.util.Map;
import java.util.Objects;
import java.util.Optional;
import java.util.stream.Collectors;

@Service
public class AdminReservaService {

    private final ReservaRepository reservaRepository;
    private final ReservaGestionRepository reservaGestionRepository;
    private final FacultadRepository facultadRepository;
    private final EscuelaRepository escuelaRepository;
    private final EspacioRepository espacioRepository;
    private final AdministrativoRepository administrativoRepository;

    public AdminReservaService(
            ReservaRepository reservaRepository,
            ReservaGestionRepository reservaGestionRepository,
            FacultadRepository facultadRepository,
            EscuelaRepository escuelaRepository,
            EspacioRepository espacioRepository,
            AdministrativoRepository administrativoRepository
    ) {
        this.reservaRepository = reservaRepository;
        this.reservaGestionRepository = reservaGestionRepository;
        this.facultadRepository = facultadRepository;
        this.escuelaRepository = escuelaRepository;
        this.espacioRepository = espacioRepository;
        this.administrativoRepository = administrativoRepository;
    }

    @Transactional(readOnly = true)
    public AdminReservaListResponse obtenerReservas(AdminReservaFilter filter) {
        String estado = filter.estadoOpt().map(this::normalizarEstado).orElse(null);
        String tipoEspacio = filter.tipoEspacioOpt().map(this::normalizarTipo).orElse(null);
        Integer facultadId = filter.facultadIdOpt().orElse(null);
        Integer escuelaId = filter.escuelaIdOpt().orElse(null);
        LocalDate fecha = filter.fechaReservaOpt().orElse(null);
        String search = filter.searchOpt().map(String::trim).map(String::toLowerCase).orElse(null);

        if (esSupervisor(filter.rolOpt())) {
            Integer usuarioId = filter.usuarioIdOpt().orElseThrow(() ->
                    new ResponseStatusException(HttpStatus.BAD_REQUEST,
                            "Se requiere el identificador del usuario supervisor."));
            SupervisorAssignment assignment = obtenerAsignacionSupervisor(usuarioId);
            facultadId = assignment.facultadId();
            escuelaId = assignment.escuelaId();
        }


        List<AdminReservaCardDto> reservas = reservaRepository.buscarReservasParaAdmin(
                        estado, tipoEspacio, facultadId, escuelaId, fecha, search)
                .stream()
                .map(AdminReservaMapper::toDto)
                .toList();

        AdminReservaSummaryDto resumen = construirResumen(
                reservaRepository.obtenerResumenEstados(tipoEspacio, facultadId, escuelaId, fecha, search)
        );

        return new AdminReservaListResponse(reservas, resumen);
    }

    @Transactional(readOnly = true)
    public AdminReservaFiltersResponse obtenerFiltros(Integer usuarioId, String rol) {
        List<String> tipos = espacioRepository.obtenerTiposDeEspacio();

        if (esSupervisor(rol)) {
            if (usuarioId == null) {
                throw new ResponseStatusException(HttpStatus.BAD_REQUEST,
                        "Se requiere el identificador del usuario supervisor.");
            }
            SupervisorAssignment assignment = obtenerAsignacionSupervisor(usuarioId);

            List<SimpleOptionDto> facultades = List.of(
                    new SimpleOptionDto(assignment.facultadId(), assignment.facultadNombre())
            );

            List<EscuelaOptionDto> escuelas = List.of(
                    new EscuelaOptionDto(assignment.escuelaId(), assignment.escuelaNombre(), assignment.facultadId())
            );

            return new AdminReservaFiltersResponse(tipos, facultades, escuelas);
        }

        List<SimpleOptionDto> facultades = facultadRepository.findAll().stream()
                .sorted(Comparator.comparing(Facultad::getNombre, String.CASE_INSENSITIVE_ORDER))
                .map(facultad -> new SimpleOptionDto(facultad.getId(), facultad.getNombre()))
                .toList();

        List<EscuelaOptionDto> escuelas = escuelaRepository.findAll().stream()
                .sorted(Comparator.comparing(Escuela::getNombre, String.CASE_INSENSITIVE_ORDER))
                .map(escuela -> new EscuelaOptionDto(escuela.getId(), escuela.getNombre(), escuela.getFacultadId()))
                .toList();

        return new AdminReservaFiltersResponse(tipos, facultades, escuelas);
    }

    @Transactional
    public AdminReservaCardDto gestionarReserva(Integer reservaId, AdminGestionReservaRequest request) {
        Reserva reserva = reservaRepository.findById(reservaId)
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "La reserva solicitada no existe"));

        ReservaGestionAccion accion;
        try {
            accion = ReservaGestionAccion.fromValue(request.accion());
        } catch (IllegalArgumentException ex) {
            throw new ResponseStatusException(HttpStatus.BAD_REQUEST, ex.getMessage());
        }

        if (Objects.equals(reserva.getEstado(), accion.estadoResultado())) {
            throw new ResponseStatusException(HttpStatus.CONFLICT,
                    "La reserva ya se encuentra en el estado solicitado");
        }

        reserva.setEstado(accion.estadoResultado());
        reservaRepository.save(reserva);

        ReservaGestion gestion = new ReservaGestion();
        gestion.setReservaId(reserva.getId());
        gestion.setUsuarioGestionId(request.usuarioGestionId());
        gestion.setAccion(accion.accionBd());
        gestion.setMotivo(request.motivo().trim());
        gestion.setComentarios(request.comentarios() != null ? request.comentarios().trim() : null);
        reservaGestionRepository.save(gestion);

        AdminReservaProjection projection = reservaRepository.buscarReservaPorId(reserva.getId());
        if (projection == null) {
            throw new ResponseStatusException(HttpStatus.INTERNAL_SERVER_ERROR,
                    "No fue posible obtener la información actualizada de la reserva");
        }
        return AdminReservaMapper.toDto(projection);
    }

    private AdminReservaSummaryDto construirResumen(List<AdminReservaEstadoProjection> datos) {
        if (datos == null || datos.isEmpty()) {
            return AdminReservaSummaryDto.empty();
        }
        Map<String, Long> porEstado = datos.stream()
                .collect(Collectors.toMap(AdminReservaEstadoProjection::getEstado, AdminReservaEstadoProjection::getTotal));

        return new AdminReservaSummaryDto(
                porEstado.getOrDefault("Pendiente", 0L),
                porEstado.getOrDefault("Aprobada", 0L),
                porEstado.getOrDefault("Rechazada", 0L),
                porEstado.getOrDefault("Cancelada", 0L)
        );
    }

    private boolean esSupervisor(String rol) {
        return rol != null && rol.trim().equalsIgnoreCase("supervisor");
    }

    private boolean esSupervisor(Optional<String> rolOpt) {
        return rolOpt.map(this::esSupervisor).orElse(false);
    }

    private SupervisorAssignment obtenerAsignacionSupervisor(Integer usuarioId) {
        Administrativo administrativo = administrativoRepository.findByUsuarioId(usuarioId)
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.FORBIDDEN,
                        "No se encontró un perfil administrativo para el usuario especificado."));

        Integer escuelaAsignadaId = administrativo.getEscuelaId();
        if (escuelaAsignadaId == null) {
            throw new ResponseStatusException(HttpStatus.FORBIDDEN,
                    "El supervisor no tiene una escuela asignada.");
        }

        Escuela escuela = escuelaRepository.findById(escuelaAsignadaId)
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND,
                        "La escuela asignada al supervisor no existe."));

        Facultad facultad = facultadRepository.findById(escuela.getFacultadId())
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND,
                        "La facultad asociada a la escuela del supervisor no existe."));

        return new SupervisorAssignment(
                escuela.getId(),
                escuela.getNombre(),
                facultad.getId(),
                facultad.getNombre()
        );
    }

    private String normalizarEstado(String estado) {
        if (estado == null) {
            return null;
        }
        return switch (estado.trim().toLowerCase(Locale.ROOT)) {
            case "pendiente" -> "Pendiente";
            case "aprobada" -> "Aprobada";
            case "rechazada" -> "Rechazada";
            case "cancelada" -> "Cancelada";
            default -> throw new ResponseStatusException(HttpStatus.BAD_REQUEST,
                    "Estado de reserva desconocido: " + estado);
        };
    }

    private String normalizarTipo(String tipo) {
        if (tipo == null) {
            return null;
        }
        String sanitized = tipo.trim();
        if (sanitized.isEmpty()) {
            return null;
        }
        return sanitized.substring(0, 1).toUpperCase(Locale.ROOT) + sanitized.substring(1).toLowerCase(Locale.ROOT);
    }

    private record SupervisorAssignment(Integer escuelaId, String escuelaNombre,
                                        Integer facultadId, String facultadNombre) {
    }
}