package com.inicidencia.repository;

import com.inicidencia.dto.IncidenciaGestionRow;
import com.inicidencia.model.Incidencia;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;
import org.springframework.stereotype.Repository;

import java.util.List;

@Repository
public interface IncidenciaRepository extends JpaRepository<Incidencia, Integer> {
    List<Incidencia> findByReservaIdOrderByFechaReporteDesc(Integer reservaId);

    @Query("""
            SELECT new com.inicidencia.dto.IncidenciaGestionRow(
                i.id,
                r.id,
                u.id,
                u.nombre,
                u.apellido,
                u.numeroDocumento,
                e.id,
                e.codigo,
                e.nombre,
                esc.id,
                esc.nombre,
                fac.id,
                fac.nombre,
                i.descripcion,
                i.fechaReporte
            )
            FROM Incidencia i
            JOIN i.reserva r
            JOIN r.espacio e
            JOIN e.escuela esc
            JOIN esc.facultad fac
            LEFT JOIN r.usuario u
            WHERE (:escuelaId IS NULL OR esc.id = :escuelaId)
              AND (:facultadId IS NULL OR fac.id = :facultadId)
              AND (:espacioId IS NULL OR e.id = :espacioId)
              AND (
                    :search IS NULL OR :search = '' OR
                    LOWER(i.descripcion) LIKE LOWER(CONCAT('%', :search, '%')) OR
                    LOWER(e.nombre) LIKE LOWER(CONCAT('%', :search, '%')) OR
                    LOWER(e.codigo) LIKE LOWER(CONCAT('%', :search, '%')) OR
                    LOWER(COALESCE(u.nombre, '')) LIKE LOWER(CONCAT('%', :search, '%')) OR
                    LOWER(COALESCE(u.apellido, '')) LIKE LOWER(CONCAT('%', :search, '%')) OR
                    LOWER(CONCAT(COALESCE(u.nombre, ''), ' ', COALESCE(u.apellido, ''))) LIKE LOWER(CONCAT('%', :search, '%')) OR
                    LOWER(COALESCE(u.numeroDocumento, '')) LIKE LOWER(CONCAT('%', :search, '%'))
              )
            ORDER BY i.fechaReporte DESC
            """)
    List<IncidenciaGestionRow> buscarParaGestion(
            @Param("facultadId") Long facultadId,
            @Param("escuelaId") Long escuelaId,
            @Param("espacioId") Integer espacioId,
            @Param("search") String search
    );
}