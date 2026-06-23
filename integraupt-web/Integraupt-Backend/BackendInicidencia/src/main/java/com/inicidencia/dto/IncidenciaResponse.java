package com.inicidencia.dto;

import com.inicidencia.model.Incidencia;

import java.time.LocalDateTime;

public record IncidenciaResponse(
        Integer id,
        Integer reservaId,
        String descripcion,
        LocalDateTime fechaReporte
) {
    public static IncidenciaResponse fromEntity(Incidencia incidencia) {
        return new IncidenciaResponse(
                incidencia.getId(),
                incidencia.getReservaId(),
                incidencia.getDescripcion(),
                incidencia.getFechaReporte()
        );
    }
}