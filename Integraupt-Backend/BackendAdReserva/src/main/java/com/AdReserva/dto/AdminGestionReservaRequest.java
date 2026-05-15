package com.AdReserva.dto;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Size;

public record AdminGestionReservaRequest(
        @NotNull(message = "El identificador del usuario gestor es obligatorio")
        Integer usuarioGestionId,
        @NotBlank(message = "La acción a ejecutar es obligatoria")
        String accion,
        @NotBlank(message = "Debes registrar un motivo para la gestión realizada")
        @Size(max = 1000, message = "El motivo no debe superar los 1000 caracteres")
        String motivo,
        @Size(max = 1000, message = "Los comentarios no deben superar los 1000 caracteres")
        String comentarios
) {
}