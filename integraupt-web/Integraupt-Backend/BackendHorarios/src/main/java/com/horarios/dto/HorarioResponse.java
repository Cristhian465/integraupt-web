package com.horarios.dto;

public record HorarioResponse(
        Integer id,
        Integer espacioId,
        String espacioNombre,
        String espacioCodigo,
        Integer bloqueId,
        String bloqueNombre,
        String horaInicio,
        String horaFin,
        String diaSemana,
        boolean ocupado
) {
}