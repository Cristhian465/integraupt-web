package com.Sanciones.repository;

import com.Sanciones.model.Docente;
import org.springframework.data.domain.Pageable;
import org.springframework.data.jpa.repository.EntityGraph;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;

import java.util.List;
import java.util.Optional;

public interface DocenteRepository extends JpaRepository<Docente, Long> {

    @EntityGraph(attributePaths = {"usuario", "escuela", "escuela.facultad"})
    Optional<Docente> findByUsuarioId(Long usuarioId);

    @EntityGraph(attributePaths = {"usuario", "escuela", "escuela.facultad"})
    Optional<Docente> findByCodigoDocenteIgnoreCase(String codigoDocente);

    @Query("""
            SELECT d FROM Docente d
            JOIN FETCH d.usuario u
            LEFT JOIN FETCH d.escuela esc
            LEFT JOIN FETCH esc.facultad fac
            WHERE (:escuelaId IS NULL OR esc.id = :escuelaId)
              AND (:facultadId IS NULL OR fac.id = :facultadId)
              AND (
                  :query IS NULL OR :query = '' OR
                  LOWER(u.nombre) LIKE LOWER(CONCAT('%', :query, '%')) OR
                  LOWER(u.apellido) LIKE LOWER(CONCAT('%', :query, '%')) OR
                  LOWER(CONCAT(u.nombre, ' ', u.apellido)) LIKE LOWER(CONCAT('%', :query, '%')) OR
                  LOWER(d.codigoDocente) LIKE LOWER(CONCAT('%', :query, '%'))
              )
            ORDER BY u.apellido ASC, u.nombre ASC
            """)
    List<Docente> buscar(@Param("query") String query,
                         @Param("facultadId") Long facultadId,
                         @Param("escuelaId") Long escuelaId,
                         Pageable pageable);

    @Query("""
            SELECT d FROM Docente d
            JOIN FETCH d.usuario u
            LEFT JOIN FETCH d.escuela esc
            LEFT JOIN FETCH esc.facultad fac
            WHERE LOWER(CONCAT(TRIM(u.nombre), ' ', TRIM(u.apellido))) = LOWER(:nombreCompleto)
            """)
    List<Docente> buscarPorNombreCompleto(@Param("nombreCompleto") String nombreCompleto);
}