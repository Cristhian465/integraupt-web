package com.reserva.dto;

import java.time.LocalDate;

public record SancionVerificacionResponse(
        boolean sancionado,
        Long sancionId,
        String motivo,
        LocalDate fechaInicio,
        LocalDate fechaFin,
        String estado
) {
}