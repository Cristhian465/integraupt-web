package com.horarios.dto;

public record HorarioCursoResponse(
        Integer id,
        Integer cursoId,
        String cursoNombre,
        String cursoCiclo,
        Integer docenteId,
        String docenteNombre,
        Integer espacioId,
        String espacioNombre,
        Integer bloqueId,
        String bloqueNombre,
        String horaInicio,
        String horaFin,
        String diaSemana,
        String fechaInicio,
        String fechaFin,
        boolean estado
) {
}