package com.horarios.dto;

import jakarta.validation.constraints.NotNull;

public record HorarioRequest(
        @NotNull(message = "El espacio es obligatorio") Integer espacioId,
        @NotNull(message = "El bloque horario es obligatorio") Integer bloqueId,
        @NotNull(message = "El día de la semana es obligatorio") String diaSemana,
        Boolean ocupado
) {
}