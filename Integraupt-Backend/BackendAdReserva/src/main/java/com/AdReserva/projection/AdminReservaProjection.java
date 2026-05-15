package com.AdReserva.repository.projection;

import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.LocalTime;

public interface AdminReservaProjection {
    Integer getReservaId();
    String getEstado();
    LocalDate getFechaReserva();
    LocalDateTime getFechaSolicitud();
    String getDescripcionUso();
    Integer getCantidadEstudiantes();
    String getEspacioCodigo();
    String getEspacioNombre();
    String getEspacioTipo();
    Integer getEscuelaId();
    String getEscuelaNombre();
    Integer getFacultadId();
    String getFacultadNombre();
    String getBloqueNombre();
    LocalTime getHoraInicio();
    LocalTime getHoraFin();
    String getCursoNombre();
    Integer getSolicitanteId();
    String getSolicitanteNombre();
    String getSolicitanteApellido();
    String getSolicitanteCorreo();
    String getUltimaAccion();
    String getMotivo();
    String getComentarios();
    LocalDateTime getFechaGestion();
    Integer getGestorId();
    String getGestorNombre();
    String getGestorApellido();
}