package com.reserva.dto;

import com.reserva.model.Curso;

public record CursoComboResponse(
        Integer id,
        String nombre,
        String ciclo
) {
    public static CursoComboResponse fromEntity(Curso curso) {
        if (curso == null) {
            return new CursoComboResponse(null, null, null);
        }
        return new CursoComboResponse(
                curso.getId(),
                curso.getNombre(),
                curso.getCiclo()
        );
    }
}