package com.reserva.dto;

import com.reserva.model.BloqueHorario;
import com.reserva.model.Espacio;
import com.reserva.model.Reserva;

import java.time.format.DateTimeFormatter;

public record ReservaUsuarioResumen(
        Integer reservaId,
        Integer espacioId,
        String espacioNombre,
        String espacioCodigo,
        String fechaReserva,
        String horaInicio,
        String horaFin,
        String estado
) {
    private static final DateTimeFormatter TIME_FORMATTER = DateTimeFormatter.ofPattern("HH:mm");

    public static ReservaUsuarioResumen from(Reserva reserva, Espacio espacio, BloqueHorario bloque) {
        if (reserva == null) {
            return null;
        }

        String fechaReserva = reserva.getFechaReserva() != null
                ? reserva.getFechaReserva().toString()
                : null;
        String horaInicio = null;
        String horaFin = null;

        if (bloque != null) {
            if (bloque.getHoraInicio() != null) {
                horaInicio = TIME_FORMATTER.format(bloque.getHoraInicio());
            }
            if (bloque.getHoraFinal() != null) {
                horaFin = TIME_FORMATTER.format(bloque.getHoraFinal());
            }
        }

        String espacioNombre = espacio != null ? espacio.getNombre() : null;
        String espacioCodigo = espacio != null ? espacio.getCodigo() : null;

        return new ReservaUsuarioResumen(
                reserva.getId(),
                reserva.getEspacioId(),
                espacioNombre,
                espacioCodigo,
                fechaReserva,
                horaInicio,
                horaFin,
                reserva.getEstado()
        );
    }
}