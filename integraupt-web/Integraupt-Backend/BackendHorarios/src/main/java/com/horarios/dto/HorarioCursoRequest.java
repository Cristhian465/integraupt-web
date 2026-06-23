package com.horarios.dto;

import jakarta.validation.constraints.NotNull;

import java.time.LocalDate;

public record HorarioCursoRequest(
        @NotNull(message = "El curso es obligatorio") Integer cursoId,
        @NotNull(message = "El docente es obligatorio") Integer docenteId,
        @NotNull(message = "El espacio es obligatorio") Integer espacioId,
        @NotNull(message = "El bloque es obligatorio") Integer bloqueId,
        @NotNull(message = "El día de la semana es obligatorio") String diaSemana,
        @NotNull(message = "La fecha de inicio es obligatoria") LocalDate fechaInicio,
        @NotNull(message = "La fecha de fin es obligatoria") LocalDate fechaFin,
        @NotNull(message = "El estado es obligatorio") Boolean estado
) {

}