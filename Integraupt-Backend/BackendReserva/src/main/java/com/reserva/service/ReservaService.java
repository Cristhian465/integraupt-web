package com.reserva.service;

import com.reserva.dto.ReservaActualizacionRequest;
import com.reserva.dto.ReservaCreacionResponse;
import com.reserva.dto.ReservaUsuarioResumen;
import com.reserva.dto.ReservaQrResponse;
import com.reserva.model.BloqueHorario;
import com.reserva.model.Espacio;
import com.reserva.model.Reserva;
import com.reserva.repository.BloqueHorarioRepository;
import com.reserva.repository.EspacioRepository;
import com.reserva.repository.ReservaRepository;
import org.springframework.stereotype.Service;
import com.reserva.exception.ReservaValidationException;
import org.springframework.transaction.annotation.Transactional;
import com.reserva.dto.ReservaDetalleResponse;

import java.util.Collections;
import java.util.List;
import java.util.Locale;
import java.util.Map;
import java.util.Objects;
import java.util.Optional;
import java.util.Set;
import java.util.function.Function;
import java.util.stream.Collectors;
import java.time.Duration;
import java.time.LocalDate;
import java.time.LocalDateTime;

@Service
@Transactional
public class ReservaService {

    private static final Map<String, String> ESTADOS_NORMALIZADOS = Map.ofEntries(
            Map.entry("pendiente", "Pendiente"),
            Map.entry("aprobado", "Aprobada"),
            Map.entry("aprobada", "Aprobada"),
            Map.entry("cancelado", "Cancelada"),
            Map.entry("cancelada", "Cancelada"),
            Map.entry("rechazado", "Rechazada"),
            Map.entry("rechazada", "Rechazada")
    );

    private final ReservaRepository reservaRepository;
    private final EspacioRepository espacioRepository;
    private final BloqueHorarioRepository bloqueHorarioRepository;
    private final ReservaQrClient reservaQrClient;

    public ReservaService(ReservaRepository reservaRepository,
                          EspacioRepository espacioRepository,
                          BloqueHorarioRepository bloqueHorarioRepository,
                          ReservaQrClient reservaQrClient) {
        this.reservaRepository = reservaRepository;
        this.espacioRepository = espacioRepository;
        this.bloqueHorarioRepository = bloqueHorarioRepository;
        this.reservaQrClient = reservaQrClient;
    }

    public List<Reserva> listarTodas() {
        return reservaRepository.findAll();
    }

    public Optional<Reserva> buscarPorId(Integer id) {
        return reservaRepository.findById(id);
    }

    public List<ReservaDetalleResponse> listarPorUsuario(Integer usuarioId, List<String> estados) {
        if (usuarioId == null) {
            return Collections.emptyList();
        }

        List<Reserva> reservas = obtenerReservasFiltradas(usuarioId, estados);

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

        return reservas.stream()
                .map(reserva -> {
                    BloqueHorario bloque = bloques.get(reserva.getBloqueId());
                    Espacio espacio = espacios.get(reserva.getEspacioId());
                    return ReservaDetalleResponse.from(reserva, espacio, bloque);
                })
                .filter(Objects::nonNull)
                .toList();
    }

    private List<Reserva> obtenerReservasFiltradas(Integer usuarioId, List<String> estados) {

        if (estados == null || estados.isEmpty()) {
            return reservaRepository.findByUsuarioId(usuarioId);
        }

        List<String> estadosNormalizados = estados.stream()
                .filter(Objects::nonNull)
                .map(estado -> estado.trim().toLowerCase(Locale.ROOT))
                .filter(estado -> !estado.isEmpty())
                .map(estado -> estado.substring(0, 1).toUpperCase(Locale.ROOT) + estado.substring(1))
                .collect(Collectors.toList());

        if (estadosNormalizados.isEmpty()) {
            return reservaRepository.findByUsuarioId(usuarioId);
        }

        return reservaRepository.findByUsuarioIdAndEstadoIn(usuarioId, estadosNormalizados);
    }

    public List<ReservaUsuarioResumen> listarResumenPorUsuario(Integer usuarioId) {
        if (usuarioId == null) {
            return List.of();
        }

        List<Reserva> reservas = reservaRepository.findTop20ByUsuarioIdOrderByFechaReservaDesc(usuarioId);
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

        return reservas.stream()
                .map(reserva -> {
                    BloqueHorario bloque = bloques.get(reserva.getBloqueId());
                    if (bloque == null) {
                        return null;
                    }

                    Espacio espacio = espacios.get(reserva.getEspacioId());
                    return ReservaUsuarioResumen.from(reserva, espacio, bloque);
                })
                .filter(Objects::nonNull)
                .toList();
    }

    public ReservaCreacionResponse crearReserva(Reserva reserva) {
        validarDuplicidadReserva(
                reserva.getUsuarioId(),
                reserva.getEspacioId(),
                reserva.getBloqueId(),
                reserva.getFechaReserva(),
                null);
        Espacio espacio = espacioRepository.findById(reserva.getEspacioId())
                .orElseThrow(() -> new IllegalArgumentException("Espacio no encontrado con el id proporcionado"));
        validarCantidadEstudiantes(reserva.getCantidadEstudiantes(), espacio);
        if (reserva.getEstado() == null || reserva.getEstado().isBlank()) {
            reserva.setEstado("Pendiente");
        }
        Reserva reservaGuardada = reservaRepository.save(reserva);

        BloqueHorario bloque = bloqueHorarioRepository.findById(reservaGuardada.getBloqueId()).orElse(null);
        ReservaQrResponse qrResponse = reservaQrClient
                .generarQr(reservaGuardada, espacio, bloque)
                .orElse(null);

        return new ReservaCreacionResponse(reservaGuardada, qrResponse);
    }

    public Reserva actualizarReserva(Integer id, ReservaActualizacionRequest datosReserva) {
        return reservaRepository.findById(id)
                .map(reservaExistente -> {
                    if (reservaExistente.getEstado() != null
                            && !reservaExistente.getEstado().trim().equalsIgnoreCase("Pendiente")) {
                        if (puedeCancelarReserva(reservaExistente, datosReserva)) {
                            reservaExistente.setEstado(normalizarEstado(datosReserva.getEstado()));
                            return reservaRepository.save(reservaExistente);
                        }
                        throw new ReservaValidationException(
                                "Solo se pueden editar reservas que se encuentran en estado Pendiente.");
                    }
                    if (!Objects.equals(reservaExistente.getUsuarioId(), datosReserva.getUsuarioId())) {
                        throw new ReservaValidationException("Solo el propietario puede editar la reserva.");
                    }

                    if (!Objects.equals(reservaExistente.getEspacioId(), datosReserva.getEspacioId())) {
                        throw new ReservaValidationException(
                                "No se puede modificar el espacio asociado a la reserva desde esta opción.");
                    }

                    Espacio espacio = espacioRepository.findById(reservaExistente.getEspacioId())
                            .orElseThrow(() -> new IllegalArgumentException("Espacio no encontrado con el id proporcionado"));
                    validarCantidadEstudiantes(datosReserva.getCantidadEstudiantes(), espacio);

                    reservaExistente.setBloqueId(datosReserva.getBloqueId());
                    reservaExistente.setCursoId(datosReserva.getCursoId());
                    reservaExistente.setFechaReserva(datosReserva.getFechaReserva());
                    reservaExistente.setDescripcionUso(datosReserva.getDescripcionUso());
                    reservaExistente.setCantidadEstudiantes(datosReserva.getCantidadEstudiantes());

                    // CORREGIDO: bloque if/else bien formado
                    if (datosReserva.getEstado() != null && !datosReserva.getEstado().isBlank()) {
                        reservaExistente.setEstado(normalizarEstado(datosReserva.getEstado()));
                    } else if (reservaExistente.getEstado() == null || reservaExistente.getEstado().isBlank()) {
                        reservaExistente.setEstado("Pendiente");
                    }

                    validarDuplicidadReserva(
                            reservaExistente.getUsuarioId(),
                            reservaExistente.getEspacioId(),
                            reservaExistente.getBloqueId(),
                            reservaExistente.getFechaReserva(),
                            reservaExistente.getId());

                    Reserva reservaActualizada = reservaRepository.save(reservaExistente);

                    BloqueHorario bloque = bloqueHorarioRepository.findById(reservaActualizada.getBloqueId())
                            .orElse(null);

                    reservaQrClient.generarQr(reservaActualizada, espacio, bloque);

                    return reservaActualizada;
                })
                .orElseThrow(() -> new IllegalArgumentException("Reserva no encontrada con el id proporcionado"));
    }

    public Optional<ReservaCreacionResponse> obtenerReservaConQr(Integer id) {
        return reservaRepository.findById(id).map(reserva -> {
            Espacio espacio = espacioRepository.findById(reserva.getEspacioId()).orElse(null);
            BloqueHorario bloque = bloqueHorarioRepository.findById(reserva.getBloqueId()).orElse(null);
            ReservaQrResponse qrResponse = reservaQrClient.obtenerQrExistente(reserva.getId().longValue())
                    .orElseGet(() -> reservaQrClient.generarQr(reserva, espacio, bloque).orElse(null));
            return new ReservaCreacionResponse(reserva, qrResponse);
        });
    }

    public void eliminarReserva(Integer id) {
        if (!reservaRepository.existsById(id)) {
            throw new IllegalArgumentException("Reserva no encontrada con el id proporcionado");
        }
        reservaRepository.deleteById(id);
    }

    private void validarCantidadEstudiantes(Integer cantidadEstudiantes, Espacio espacio) {
        if (cantidadEstudiantes == null || cantidadEstudiantes < 1) {
            throw new ReservaValidationException("La cantidad de estudiantes debe ser mayor a cero.");
        }

        Integer capacidad = espacio.getCapacidad();
        if (capacidad != null && cantidadEstudiantes > capacidad) {
            throw new ReservaValidationException(String.format(
                    "La cantidad de estudiantes (%d) supera la capacidad del espacio (%d).",
                    cantidadEstudiantes,
                    capacidad));
        }
    }

    private void validarDuplicidadReserva(
            Integer usuarioId,
            Integer espacioId,
            Integer bloqueId,
            LocalDate fechaReserva,
            Integer reservaActualId) {
        if (usuarioId == null || espacioId == null || bloqueId == null || fechaReserva == null) {
            return;
        }

        reservaRepository
                .findByUsuarioIdAndBloqueIdAndFechaReserva(usuarioId, bloqueId, fechaReserva)
                .ifPresent(reservaExistente -> {
                    if (reservaActualId == null || !reservaExistente.getId().equals(reservaActualId)) {
                        throw new ReservaValidationException(
                                "Ya existe una reserva del usuario para el bloque y fecha seleccionados.");
                    }
                });


        reservaRepository
                .findByEspacioIdAndBloqueIdAndFechaReserva(espacioId, bloqueId, fechaReserva)
                .ifPresent(reservaExistente -> {
                    if (reservaActualId == null || !reservaExistente.getId().equals(reservaActualId)) {
                        throw new ReservaValidationException(
                                "El bloque seleccionado ya fue reservado en este espacio para la fecha indicada.");
                    }
                });
    }
    private boolean puedeCancelarReserva(Reserva reservaExistente, ReservaActualizacionRequest datosReserva) {
        if (!esEstadoCancelado(datosReserva.getEstado())) {
            return false;
        }

        if (!esEstadoAprobado(reservaExistente.getEstado())) {
            return false;
        }

        validarAnticipacionCancelacion(reservaExistente);
        return true;
    }

    private void validarAnticipacionCancelacion(Reserva reservaExistente) {
        if (reservaExistente.getFechaReserva() == null || reservaExistente.getBloqueId() == null) {
            throw new ReservaValidationException(
                    "No es posible validar la fecha y bloque de la reserva para realizar la cancelación.");
        }

        BloqueHorario bloque = bloqueHorarioRepository.findById(reservaExistente.getBloqueId())
                .orElseThrow(() -> new ReservaValidationException(
                        "No se encontró el bloque horario asociado a la reserva."));

        if (bloque.getHoraInicio() == null) {
            throw new ReservaValidationException(
                    "El bloque horario asociado a la reserva no tiene hora de inicio configurada.");
        }

        LocalDateTime fechaHoraInicio = LocalDateTime.of(reservaExistente.getFechaReserva(), bloque.getHoraInicio());
        Duration tiempoRestante = Duration.between(LocalDateTime.now(), fechaHoraInicio);

        if (tiempoRestante.compareTo(Duration.ofHours(12)) < 0) {
            throw new ReservaValidationException(
                    "Las reservas aprobadas solo se pueden cancelar con al menos 12 horas de anticipación.");
        }
    }

    private String normalizarEstado(String estado) {
        if (estado == null) {
            return null;
        }

        String estadoNormalizado = ESTADOS_NORMALIZADOS.get(estado.trim().toLowerCase(Locale.ROOT));
        return estadoNormalizado != null ? estadoNormalizado : estado.trim();
    }

    private boolean esEstadoCancelado(String estado) {
        return "Cancelada".equalsIgnoreCase(normalizarEstado(estado));
    }

    private boolean esEstadoAprobado(String estado) {
        return "Aprobada".equalsIgnoreCase(normalizarEstado(estado));
    }
}
