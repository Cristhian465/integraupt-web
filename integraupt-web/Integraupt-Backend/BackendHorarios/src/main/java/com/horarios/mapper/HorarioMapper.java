package com.horarios.mapper;

import com.horarios.dto.HorarioResponse;
import com.horarios.model.Horario;

import java.time.format.DateTimeFormatter;

public final class HorarioMapper {

    private static final DateTimeFormatter TIME_FORMATTER = DateTimeFormatter.ofPattern("HH:mm");

    private HorarioMapper() {
    }

    public static HorarioResponse toResponse(Horario horario) {
        return new HorarioResponse(
                horario.getId(),
                horario.getEspacio().getId(),
                horario.getEspacio().getNombre(),
                horario.getEspacio().getCodigo(),
                horario.getBloque().getId(),
                horario.getBloque().getNombre(),
                horario.getBloque().getHoraInicio().format(TIME_FORMATTER),
                horario.getBloque().getHoraFin().format(TIME_FORMATTER),
                horario.getDiaSemana().getNombre(),
                horario.isOcupado()
        );
    }
}