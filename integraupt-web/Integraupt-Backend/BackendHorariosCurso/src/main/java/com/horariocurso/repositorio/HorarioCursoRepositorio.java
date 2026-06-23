package com.horariocurso.repositorio;

import com.horariocurso.modelo.HorarioCurso;
import com.horariocurso.projection.HorarioCursoDetalleProjection;
import java.util.List;
import java.util.Optional;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;
import org.springframework.stereotype.Repository;

@Repository
public interface HorarioCursoRepositorio extends JpaRepository<HorarioCurso, Integer> {

    @Query(value = """
            SELECT hc.IdHorarioCurso      AS idHorarioCurso,
                   hc.Curso               AS curso,
                   hc.Docente             AS docente,
                   hc.Espacio             AS espacio,
                   hc.Bloque              AS bloque,
                   hc.DiaSemana           AS diaSemana,
                   hc.FechaInicio         AS fechaInicio,
                   hc.FechaFin            AS fechaFin,
                   hc.Estado              AS estado,
                   c.Nombre               AS nombreCurso,
                   CONCAT(u.Nombre, ' ', u.Apellido) AS nombreDocente,
                   e.Nombre               AS nombreEspacio,
                   e.Codigo               AS codigoEspacio,
                   b.Nombre               AS nombreBloque,
                   DATE_FORMAT(b.HoraInicio, '%H:%i') AS horaInicio,
                   DATE_FORMAT(b.HoraFinal, '%H:%i') AS horaFinal
            FROM horario_curso hc
            LEFT JOIN cursos c ON c.IdCurso = hc.Curso
            LEFT JOIN usuario u ON u.IdUsuario = hc.Docente
            LEFT JOIN espacio e ON e.IdEspacio = hc.Espacio
            LEFT JOIN bloqueshorarios b ON b.IdBloque = hc.Bloque
            ORDER BY hc.IdHorarioCurso DESC
            """, nativeQuery = true)
    List<HorarioCursoDetalleProjection> listarDetalle();

    @Query(value = """
            SELECT hc.IdHorarioCurso      AS idHorarioCurso,
                   hc.Curso               AS curso,
                   hc.Docente             AS docente,
                   hc.Espacio             AS espacio,
                   hc.Bloque              AS bloque,
                   hc.DiaSemana           AS diaSemana,
                   hc.FechaInicio         AS fechaInicio,
                   hc.FechaFin            AS fechaFin,
                   hc.Estado              AS estado,
                   c.Nombre               AS nombreCurso,
                   CONCAT(u.Nombre, ' ', u.Apellido) AS nombreDocente,
                   e.Nombre               AS nombreEspacio,
                   e.Codigo               AS codigoEspacio,
                   b.Nombre               AS nombreBloque,
                   DATE_FORMAT(b.HoraInicio, '%H:%i') AS horaInicio,
                   DATE_FORMAT(b.HoraFinal, '%H:%i') AS horaFinal
            FROM horario_curso hc
            LEFT JOIN cursos c ON c.IdCurso = hc.Curso
            LEFT JOIN usuario u ON u.IdUsuario = hc.Docente
            LEFT JOIN espacio e ON e.IdEspacio = hc.Espacio
            LEFT JOIN bloqueshorarios b ON b.IdBloque = hc.Bloque
            WHERE hc.IdHorarioCurso = :id
            """, nativeQuery = true)
    Optional<HorarioCursoDetalleProjection> buscarDetallePorId(@Param("id") Integer id);
}
