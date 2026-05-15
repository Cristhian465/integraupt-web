package com.inicidencia.dto;

import java.time.LocalDateTime;

public record DisponibilidadIncidenciaResponse(
        Integer reservaId,
        boolean habilitado,
        LocalDateTime habilitadoDesde,
        LocalDateTime habilitadoHasta
) {
    public static DisponibilidadIncidenciaResponse fueraDeRango(Integer reservaId, LocalDateTime habilitadoDesde, LocalDateTime habilitadoHasta) {
        return new DisponibilidadIncidenciaResponse(reservaId, false, habilitadoDesde, habilitadoHasta);
    }

    public static DisponibilidadIncidenciaResponse disponible(Integer reservaId, LocalDateTime habilitadoDesde, LocalDateTime habilitadoHasta) {
        return new DisponibilidadIncidenciaResponse(reservaId, true, habilitadoDesde, habilitadoHasta);
    }
}