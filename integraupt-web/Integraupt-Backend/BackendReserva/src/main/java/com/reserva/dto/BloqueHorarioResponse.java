package com.reserva.dto;

import com.reserva.model.BloqueHorario;

import java.time.LocalTime;

public record BloqueHorarioResponse(
        Integer id,
        String nombre,
        Integer orden,
        LocalTime horaInicio,
        LocalTime horaFinal
) {
    public static BloqueHorarioResponse fromEntity(BloqueHorario bloque) {
        if (bloque == null) {
            return new BloqueHorarioResponse(null, null, null, null, null);
        }
        return new BloqueHorarioResponse(
                bloque.getId(),
                bloque.getNombre(),
                bloque.getOrden(),
                bloque.getHoraInicio(),
                bloque.getHoraFinal()
        );
    }
}