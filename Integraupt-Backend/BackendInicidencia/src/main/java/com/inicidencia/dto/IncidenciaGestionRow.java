package com.inicidencia.dto;

import java.time.LocalDateTime;

public record IncidenciaGestionRow(
        Integer id,
        Integer reservaId,
        Integer usuarioId,
        String usuarioNombre,
        String usuarioApellido,
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