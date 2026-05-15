package com.inicidencia.service;

import com.inicidencia.dto.DisponibilidadIncidenciaResponse;
import com.inicidencia.dto.IncidenciaGestionResponse;
import com.inicidencia.dto.IncidenciaGestionRow;
import com.inicidencia.dto.IncidenciaRequest;
import com.inicidencia.dto.IncidenciaResponse;
import com.inicidencia.dto.ReservaIncidenciaResumen;
import com.inicidencia.model.BloqueHorario;
import com.inicidencia.model.Incidencia;
import com.inicidencia.model.Espacio;
import com.inicidencia.model.Reserva;
import com.inicidencia.model.RolUsuarioBusqueda;
import com.inicidencia.repository.BloqueHorarioRepository;
import com.inicidencia.repository.IncidenciaRepository;
import com.inicidencia.repository.EspacioRepository;
import com.inicidencia.repository.ReservaRepository;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.util.StringUtils;

import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.LocalTime;
import java.util.Comparator;
import java.util.List;
import java.util.Map;
import java.util.Objects;
import java.util.Set;
import java.util.function.Function;
import java.util.stream.Collectors;

@Service
@Transactional
public class IncidenciaService {

    private final IncidenciaRepository incidenciaRepository;
    private final ReservaRepository reservaRepository;
    private final BloqueHorarioRepository bloqueHorarioRepository;
    private final EspacioRepository espacioRepository;

    public IncidenciaService(IncidenciaRepository incidenciaRepository,
                             ReservaRepository reservaRepository,
                             BloqueHorarioRepository bloqueHorarioRepository,
                             EspacioRepository espacioRepository) {
        this.incidenciaRepository = incidenciaRepository;
        this.reservaRepository = reservaRepository;
        this.bloqueHorarioRepository = bloqueHorarioRepository;
        this.espacioRepository = espacioRepository;
    }

    public IncidenciaResponse registrarIncidencia(IncidenciaRequest request) {
        Reserva reserva = obtenerReserva(request.reservaId());
        BloqueHorario bloque = obtenerBloque(reserva.getBloqueId());

        VentanaDisponibilidad ventana = calcularVentanaDisponibilidad(reserva.getFechaReserva(), bloque.getHoraInicio(), bloque.getHoraFinal());
        LocalDateTime ahora = LocalDateTime.now();

        if (!ventana.estaDentroDeVentana(ahora)) {
            throw new IllegalStateException("El formulario de incidencias ya no está disponible para esta reserva.");
        }

        Incidencia incidencia = new Incidencia();
        incidencia.setReservaId(reserva.getId());
        incidencia.setDescripcion(request.descripcion());

        Incidencia incidenciaGuardada = incidenciaRepository.save(incidencia);
        return IncidenciaResponse.fromEntity(incidenciaGuardada);
    }

    @Transactional(readOnly = true)
    public List<IncidenciaResponse> listarPorReserva(Integer reservaId) {
        return incidenciaRepository.findByReservaIdOrderByFechaReporteDesc(reservaId)
                .stream()
                .map(IncidenciaResponse::fromEntity)
                .toList();
    }

    @Transactional(readOnly = true)
    public List<IncidenciaGestionResponse> listarIncidenciasParaGestion(String rolValor,
                                                                        Long facultadId,
                                                                        Long escuelaId,
                                                                        Long escuelaContextoId,
                                                                        Integer espacioId,
                                                                        String search) {
        RolUsuarioBusqueda rol = RolUsuarioBusqueda.fromNombre(rolValor);
        FiltroContexto filtros = construirFiltroContexto(rol, facultadId, escuelaId, escuelaContextoId);
        String terminoBusqueda = StringUtils.hasText(search) ? search.trim() : null;

        return incidenciaRepository.buscarParaGestion(
                        filtros.facultadId(),
                        filtros.escuelaId(),
                        espacioId,
                        terminoBusqueda)
                .stream()
                .map(this::mapearGestion)
                .toList();
    }

    @Transactional(readOnly = true)
    public DisponibilidadIncidenciaResponse verificarDisponibilidad(Integer reservaId) {
        Reserva reserva = obtenerReserva(reservaId);
        BloqueHorario bloque = obtenerBloque(reserva.getBloqueId());

        VentanaDisponibilidad ventana = calcularVentanaDisponibilidad(reserva.getFechaReserva(), bloque.getHoraInicio(), bloque.getHoraFinal());
        LocalDateTime ahora = LocalDateTime.now();

        if (ventana.estaDentroDeVentana(ahora)) {
            return DisponibilidadIncidenciaResponse.disponible(reservaId, ventana.inicioDisponibilidad(), ventana.finDisponibilidad());
        }

        return DisponibilidadIncidenciaResponse.fueraDeRango(reservaId, ventana.inicioDisponibilidad(), ventana.finDisponibilidad());
    }

    @Transactional(readOnly = true)
    public List<ReservaIncidenciaResumen> listarReservasParaUsuario(Integer usuarioId) {
        List<Reserva> reservas = reservaRepository.findTop25ByUsuarioIdOrderByFechaReservaDesc(usuarioId);

        if (reservas.isEmpty()) {
            return List.of();
        }

        Set<Integer> bloqueIds = reservas.stream()
                .map(Reserva::getBloqueId)
                .filter(Objects::nonNull)
                .collect(Collectors.toSet());

        Map<Integer, BloqueHorario> bloques = bloqueHorarioRepository.findAllById(bloqueIds).stream()
                .collect(Collectors.toMap(BloqueHorario::getId, Function.identity()));

        Set<Integer> espacioIds = reservas.stream()
                .map(Reserva::getEspacioId)
                .filter(Objects::nonNull)
                .collect(Collectors.toSet());

        Map<Integer, Espacio> espacios = espacioRepository.findAllById(espacioIds).stream()
                .collect(Collectors.toMap(Espacio::getId, Function.identity()));

        LocalDateTime ahora = LocalDateTime.now();

        return reservas.stream()
                .map(reserva -> {
                    BloqueHorario bloque = bloques.get(reserva.getBloqueId());
                    if (bloque == null) {
                        return null;
                    }

                    VentanaDisponibilidad ventana = calcularVentanaDisponibilidad(
                            reserva.getFechaReserva(),
                            bloque.getHoraInicio(),
                            bloque.getHoraFinal()
                    );

                    boolean habilitado = ventana.estaDentroDeVentana(ahora);
                    Espacio espacio = espacios.get(reserva.getEspacioId());

                    return ReservaIncidenciaResumen.from(
                            reserva,
                            espacio,
                            bloque.getHoraInicio(),
                            bloque.getHoraFinal(),
                            ventana.inicioDisponibilidad(),
                            ventana.finDisponibilidad(),
                            habilitado
                    );
                })
                .filter(Objects::nonNull)
                .filter(ReservaIncidenciaResumen::habilitado)
                .sorted(Comparator
                        .comparing(ReservaIncidenciaResumen::habilitado, Comparator.reverseOrder())
                        .thenComparing(ReservaIncidenciaResumen::habilitadoDesde, Comparator.reverseOrder()))
                .toList();
    }

    private Reserva obtenerReserva(Integer reservaId) {
        return reservaRepository.findById(reservaId)
                .orElseThrow(() -> new IllegalArgumentException("No se encontró la reserva indicada."));
    }

    private BloqueHorario obtenerBloque(Integer bloqueId) {
        return bloqueHorarioRepository.findById(bloqueId)
                .orElseThrow(() -> new IllegalArgumentException("No se encontró el bloque horario asociado a la reserva."));
    }

    private VentanaDisponibilidad calcularVentanaDisponibilidad(LocalDate fechaReserva, LocalTime horaInicio, LocalTime horaFin) {
        LocalDateTime inicioReserva = LocalDateTime.of(fechaReserva, horaInicio);
        LocalDateTime finReserva = LocalDateTime.of(fechaReserva, horaFin);

        if (horaFin.isBefore(horaInicio) || horaFin.equals(horaInicio)) {
            finReserva = finReserva.plusDays(1);
        }

        LocalDateTime finDisponibilidad = finReserva.plusHours(24);
        return new VentanaDisponibilidad(inicioReserva, finDisponibilidad);
    }

    private record VentanaDisponibilidad(LocalDateTime inicioDisponibilidad, LocalDateTime finDisponibilidad) {
        boolean estaDentroDeVentana(LocalDateTime fechaEvaluacion) {
            return !fechaEvaluacion.isBefore(inicioDisponibilidad) && !fechaEvaluacion.isAfter(finDisponibilidad);
        }
    }
    private IncidenciaGestionResponse mapearGestion(IncidenciaGestionRow fila) {
        String nombreCompleto = combinarNombre(fila.usuarioNombre(), fila.usuarioApellido());
        return new IncidenciaGestionResponse(
                fila.id(),
                fila.reservaId(),
                fila.usuarioId(),
                normalizar(nombreCompleto),
                normalizar(fila.usuarioDocumento()),
                fila.espacioId(),
                normalizar(fila.espacioCodigo()),
                normalizar(fila.espacioNombre()),
                fila.escuelaId(),
                normalizar(fila.escuelaNombre()),
                fila.facultadId(),
                normalizar(fila.facultadNombre()),
                normalizar(fila.descripcion()),
                fila.fechaReporte()
        );
    }

    private String combinarNombre(String nombre, String apellido) {
        String nombreValor = normalizar(nombre);
        String apellidoValor = normalizar(apellido);
        if (nombreValor == null && apellidoValor == null) {
            return null;
        }
        String nombreTexto = nombreValor != null ? nombreValor : "";
        String apellidoTexto = apellidoValor != null ? apellidoValor : "";
        String combinado = (nombreTexto + " " + apellidoTexto).trim();
        return combinado.isEmpty() ? null : combinado;
    }

    private String normalizar(String valor) {
        if (!StringUtils.hasText(valor)) {
            return null;
        }
        String trimmed = valor.trim();
        return trimmed.isEmpty() ? null : trimmed;
    }

    private FiltroContexto construirFiltroContexto(RolUsuarioBusqueda rol,
                                                   Long facultadId,
                                                   Long escuelaId,
                                                   Long escuelaContextoId) {
        Long facultadFiltro = facultadId;
        Long escuelaFiltro = escuelaId;

        if (rol == RolUsuarioBusqueda.SUPERVISOR) {
            Long escuelaRestriccion = escuelaContextoId != null ? escuelaContextoId : escuelaId;
            if (escuelaRestriccion == null) {
                throw new IllegalArgumentException("Los supervisores deben tener una escuela asociada para revisar incidencias.");
            }
            escuelaFiltro = escuelaRestriccion;
            facultadFiltro = null;
        }

        return new FiltroContexto(rol, facultadFiltro, escuelaFiltro);
    }

    private record FiltroContexto(RolUsuarioBusqueda rol, Long facultadId, Long escuelaId) {
    }
}
