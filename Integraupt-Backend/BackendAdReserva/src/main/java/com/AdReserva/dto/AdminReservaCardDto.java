package com.AdReserva.dto;

import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.LocalTime;

public record AdminReservaCardDto(
        Integer id,
        String estado,
        LocalDate fechaReserva,
        LocalDateTime fechaSolicitud,
        String descripcionUso,
        Integer cantidadEstudiantes,
        String espacioCodigo,
        String espacioNombre,
        String espacioTipo,
        Integer escuelaId,
        String escuelaNombre,
        Integer facultadId,
        String facultadNombre,
        String bloqueNombre,
        LocalTime horaInicio,
        LocalTime horaFin,
        String cursoNombre,
        Integer solicitanteId,
        String solicitanteNombre,
        String solicitanteApellido,
        String solicitanteCorreo,
        String ultimaAccion,
        String motivo,
        String comentarios,
        LocalDateTime fechaGestion,
        Integer gestorId,
        String gestorNombre,
        String gestorApellido
) {
    public String solicitanteNombreCompleto() {
        return buildNombreCompleto(solicitanteNombre, solicitanteApellido);
    }

    public String gestorNombreCompleto() {
        return buildNombreCompleto(gestorNombre, gestorApellido);
    }

    private static String buildNombreCompleto(String nombres, String apellidos) {
        if (nombres == null && apellidos == null) {
            return null;
        }
        if (nombres == null) {
            return apellidos;
        }
        if (apellidos == null) {
            return nombres;
        }
        return nombres + " " + apellidos;
    }
}