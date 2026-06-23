package com.horariocurso.projection;

import java.time.LocalDate;

public interface HorarioCursoDetalleProjection {

    Integer getIdHorarioCurso();

    Integer getCurso();

    Integer getDocente();

    Integer getEspacio();

    Integer getBloque();

    String getDiaSemana();

    LocalDate getFechaInicio();

    LocalDate getFechaFin();

    Boolean getEstado();

    String getNombreCurso();

    String getNombreDocente();

    String getNombreEspacio();

    String getCodigoEspacio();

    String getNombreBloque();

    String getHoraInicio();

    String getHoraFinal();
}
