package com.inicidencia.dto;

import com.inicidencia.model.Espacio;
import com.inicidencia.model.Reserva;

import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.LocalTime;

public record ReservaIncidenciaResumen(
        Integer reservaId,
        Integer espacioId,
        String espacioNombre,
        String espacioCodigo,
        LocalDate fechaReserva,
        LocalTime horaInicio,
        LocalTime horaFin,
        String estado,
        boolean habilitado,
        LocalDateTime habilitadoDesde,
        LocalDateTime habilitadoHasta
) {

    public static ReservaIncidenciaResumen from(
            Reserva reserva,
            Espacio espacio,
            LocalTime horaInicio,
            LocalTime horaFin,
            LocalDateTime habilitadoDesde,
            LocalDateTime habilitadoHasta,
            boolean habilitado
    ) {
        String nombre = espacio != null ? espacio.getNombre() : null;
        String codigo = espacio != null ? espacio.getCodigo() : null;

        return new ReservaIncidenciaResumen(
                reserva.getId(),
                espacio != null ? espacio.getId() : reserva.getEspacioId(),
                nombre != null && !nombre.isBlank() ? nombre : "Espacio " + reserva.getEspacioId(),
                codigo != null && !codigo.isBlank() ? codigo : null,
                reserva.getFechaReserva(),
                horaInicio,
                horaFin,
                reserva.getEstado(),
                habilitado,
                habilitadoDesde,
                habilitadoHasta
        );
    }
}