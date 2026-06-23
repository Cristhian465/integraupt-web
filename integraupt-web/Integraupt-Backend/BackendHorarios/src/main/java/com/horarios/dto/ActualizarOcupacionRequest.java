package com.horarios.dto;

import jakarta.validation.constraints.NotNull;

public record ActualizarOcupacionRequest(
        @NotNull(message = "El estado de ocupación es obligatorio") Boolean ocupado
) {
}