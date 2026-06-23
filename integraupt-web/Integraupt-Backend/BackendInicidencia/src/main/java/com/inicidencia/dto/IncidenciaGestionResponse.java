package com.inicidencia.dto;

import java.time.LocalDateTime;

public record IncidenciaGestionResponse(
        Integer id,
        Integer reservaId,
        Integer usuarioId,
        String usuarioNombre,
        String usuarioDocumento,
        Integer espacioId,
        String espacioCodigo,
        String espacioNombre,
        Long escuelaId,
        String escuelaNombre,
        Long facultadId,
        String facultadNombre,
        String descripcion,
        LocalDateTime fechaReporte
) {
}