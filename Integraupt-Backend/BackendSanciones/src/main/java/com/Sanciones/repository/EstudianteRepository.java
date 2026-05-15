package com.Sanciones.repository;

import com.Sanciones.model.Estudiante;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.EntityGraph;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;

import java.util.List;
import java.util.Optional;

public interface EstudianteRepository extends JpaRepository<Estudiante, Long> {

    @EntityGraph(attributePaths = {"usuario", "escuela", "escuela.facultad"})
    Optional<Estudiante> findByUsuarioId(Long usuarioId);

    @EntityGraph(attributePaths = {"usuario", "escuela", "escuela.facultad"})
    Optional<Estudiante> findByCodigoIgnoreCase(String codigo);

    @Query("""
            SELECT e FROM Estudiante e
            JOIN FETCH e.usuario u
            JOIN FETCH e.escuela esc
            JOIN FETCH esc.facultad fac
            WHERE (:escuelaId IS NULL OR esc.id = :escuelaId)
              AND (:facultadId IS NULL OR fac.id = :facultadId)
              AND (
                  :query IS NULL OR :query = '' OR
                  LOWER(u.nombre) LIKE LOWER(CONCAT('%', :query, '%')) OR
                  LOWER(u.apellido) LIKE LOWER(CONCAT('%', :query, '%')) OR
                  LOWER(CONCAT(u.nombre, ' ', u.apellido)) LIKE LOWER(CONCAT('%', :query, '%')) OR
                  LOWER(e.codigo) LIKE LOWER(CONCAT('%', :query, '%'))
              )
            ORDER BY u.apellido ASC, u.nombre ASC
            """)
    List<Estudiante> buscar(@Param("query") String query,
                            @Param("facultadId") Long facultadId,
                            @Param("escuelaId") Long escuelaId,
                            Pageable pageable);

    @Query("""
            SELECT e FROM Estudiante e
            JOIN FETCH e.usuario u
            LEFT JOIN FETCH e.escuela esc
            LEFT JOIN FETCH esc.facultad fac
            WHERE LOWER(CONCAT(TRIM(u.nombre), ' ', TRIM(u.apellido))) = LOWER(:nombreCompleto)
            """)
    List<Estudiante> buscarPorNombreCompleto(@Param("nombreCompleto") String nombreCompleto);
}