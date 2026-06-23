package com.horarios.repository;

import com.horarios.model.DiaSemana;
import com.horarios.model.HorarioCurso;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;

import java.time.LocalDate;
import java.util.List;

public interface HorarioCursoRepository extends JpaRepository<HorarioCurso, Integer> {

    List<HorarioCurso> findByEspacioId(Integer espacioId);

    @Query("SELECT CASE WHEN COUNT(hc) > 0 THEN true ELSE false END FROM HorarioCurso hc " +
            "WHERE hc.espacio.id = :espacioId AND hc.bloque.id = :bloqueId AND hc.diaSemana = :diaSemana " +
            "AND hc.estado = true AND hc.fechaInicio <= :fechaFin AND hc.fechaFin >= :fechaInicio " +
            "AND (:ignorarId IS NULL OR hc.id <> :ignorarId)")
    boolean existeTraslapeActivo(@Param("espacioId") Integer espacioId,
                                 @Param("bloqueId") Integer bloqueId,
                                 @Param("diaSemana") DiaSemana diaSemana,
                                 @Param("fechaInicio") LocalDate fechaInicio,
                                 @Param("fechaFin") LocalDate fechaFin,
                                 @Param("ignorarId") Integer ignorarId);

    @Query("SELECT hc FROM HorarioCurso hc WHERE (:filtro IS NULL OR " +
            "LOWER(hc.curso.nombre) LIKE LOWER(CONCAT('%', :filtro, '%')) OR " +
            "LOWER(hc.docente.nombres) LIKE LOWER(CONCAT('%', :filtro, '%')) OR " +
            "LOWER(hc.docente.apellidos) LIKE LOWER(CONCAT('%', :filtro, '%')))")
    List<HorarioCurso> buscarPorFiltro(@Param("filtro") String filtro);
}