package com.horarios.mapper;

import com.horarios.dto.HorarioCursoResponse;
import com.horarios.model.HorarioCurso;

import java.time.format.DateTimeFormatter;

public final class HorarioCursoMapper {

    private static final DateTimeFormatter TIME_FORMATTER = DateTimeFormatter.ofPattern("HH:mm");
    private static final DateTimeFormatter DATE_FORMATTER = DateTimeFormatter.ISO_LOCAL_DATE;

    private HorarioCursoMapper() {
    }

    public static HorarioCursoResponse toResponse(HorarioCurso horarioCurso) {
        return new HorarioCursoResponse(
                horarioCurso.getId(),
                horarioCurso.getCurso().getId(),
                horarioCurso.getCurso().getNombre(),
                horarioCurso.getCurso().getCiclo(),
                horarioCurso.getDocente().getId(),
                horarioCurso.getDocente().getNombreCompleto(),
                horarioCurso.getEspacio().getId(),
                horarioCurso.getEspacio().getNombre(),
                horarioCurso.getBloque().getId(),
                horarioCurso.getBloque().getNombre(),
                horarioCurso.getBloque().getHoraInicio().format(TIME_FORMATTER),
                horarioCurso.getBloque().getHoraFin().format(TIME_FORMATTER),
                horarioCurso.getDiaSemana().getNombre(),
                horarioCurso.getFechaInicio().format(DATE_FORMATTER),
                horarioCurso.getFechaFin().format(DATE_FORMATTER),
                horarioCurso.isEstado()
        );
    }
}