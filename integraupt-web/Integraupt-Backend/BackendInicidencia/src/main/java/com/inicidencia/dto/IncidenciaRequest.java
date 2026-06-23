package com.inicidencia.dto;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;

public record IncidenciaRequest(
        @NotNull(message = "El identificador de la reserva es obligatorio") Integer reservaId,
        @NotBlank(message = "La descripción de la incidencia es obligatoria") String descripcion
) {
}