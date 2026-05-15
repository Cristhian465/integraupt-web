package com.reserva.dto;

import com.reserva.model.BloqueHorario;
import com.reserva.model.Espacio;
import com.reserva.model.Reserva;

import java.time.format.DateTimeFormatter;
import java.util.Objects;

public record ReservaDetalleResponse(
        Integer id,
        Integer usuarioId,
        Integer espacioId,
        String espacioNombre,
        Integer bloqueId,
        String bloqueHoraInicio,
        String bloqueHoraFin,
        Integer cursoId,
        String fechaReserva,
        String fechaSolicitud,
        String descripcionUso,
        Integer cantidadEstudiantes,
        String estado
) {
    private static final DateTimeFormatter DATE_FORMATTER = DateTimeFormatter.ISO_LOCAL_DATE;
    private static final DateTimeFormatter TIME_FORMATTER = DateTimeFormatter.ofPattern("HH:mm");

    public static ReservaDetalleResponse from(Reserva reserva, Espacio espacio, BloqueHorario bloque) {
        if (reserva == null) {
            return null;
        }

        String fechaReserva = reserva.getFechaReserva() != null
                ? DATE_FORMATTER.format(reserva.getFechaReserva())
                : null;
        String fechaSolicitud = reserva.getFechaSolicitud() != null
                ? reserva.getFechaSolicitud().toString()
                : null;

        String bloqueHoraInicio = obtenerHoraFormateada(bloque, true);
        String bloqueHoraFin = obtenerHoraFormateada(bloque, false);

        return new ReservaDetalleResponse(
                reserva.getId(),
                reserva.getUsuarioId(),
                reserva.getEspacioId(),
                espacio != null ? espacio.getNombre() : null,
                reserva.getBloqueId(),
                bloqueHoraInicio,
                bloqueHoraFin,
                reserva.getCursoId(),
                fechaReserva,
                fechaSolicitud,
                reserva.getDescripcionUso(),
                reserva.getCantidadEstudiantes(),
                reserva.getEstado()
        );
    }

    private static String obtenerHoraFormateada(BloqueHorario bloque, boolean esHoraInicio) {
        if (bloque == null) {
            return null;
        }

        return esHoraInicio
                ? Objects.nonNull(bloque.getHoraInicio()) ? TIME_FORMATTER.format(bloque.getHoraInicio()) : null
                : Objects.nonNull(bloque.getHoraFinal()) ? TIME_FORMATTER.format(bloque.getHoraFinal()) : null;
    }
}