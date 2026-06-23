package com.auditoria.repositorio.proyecciones;

import java.time.LocalDate;
import java.time.LocalDateTime;

public interface AuditoriaReservaResumenProjection {

    Integer getIdAudit();

    Integer getIdReserva();

    String getEstadoAnterior();

    String getEstadoNuevo();

    LocalDateTime getFechaCambio();

    Integer getUsuarioCambioId();

    String getUsuarioCambioNombre();

    String getUsuarioCambioDocumento();

    String getEstadoReservaActual();

    String getDescripcionUso();

    LocalDate getFechaReserva();

    String getCodigoEspacio();

    String getNombreEspacio();
}
