package com.AdReserva.repository;

import com.AdReserva.model.Reserva;
import com.AdReserva.repository.projection.AdminReservaEstadoProjection;
import com.AdReserva.repository.projection.AdminReservaProjection;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;

import java.time.LocalDate;
import java.util.List;

public interface ReservaRepository extends JpaRepository<Reserva, Integer> {

    @Query(value = """
            SELECT
                r.IdReserva AS reserva_id,
                r.Estado AS estado,
                r.fechaReserva AS fecha_reserva,
                r.fechaSolicitud AS fecha_solicitud,
                r.DescripcionUso AS descripcion_uso,
                r.CantidadEstudiantes AS cantidad_estudiantes,
                e.Codigo AS espacio_codigo,
                e.Nombre AS espacio_nombre,
                e.Tipo AS espacio_tipo,
                esc.IdEscuela AS escuela_id,
                esc.Nombre AS escuela_nombre,
                fac.IdFacultad AS facultad_id,
                fac.Nombre AS facultad_nombre,
                bh.Nombre AS bloque_nombre,
                bh.HoraInicio AS hora_inicio,
                bh.HoraFinal AS hora_fin,
                c.Nombre AS curso_nombre,
                u.IdUsuario AS solicitante_id,
                u.Nombre AS solicitante_nombre,
                u.Apellido AS solicitante_apellido,
                ua.CorreoU AS solicitante_correo,
                rg.Accion AS ultima_accion,
                rg.Motivo AS motivo,
                rg.Comentarios AS comentarios,
                rg.FechaGestion AS fecha_gestion,
                ug.IdUsuario AS gestor_id,
                ug.Nombre AS gestor_nombre,
                ug.Apellido AS gestor_apellido
            FROM reserva r
            INNER JOIN usuario u ON u.IdUsuario = r.usuario
            LEFT JOIN usuario_auth ua ON ua.IdUsuario = u.IdUsuario
            INNER JOIN espacio e ON e.IdEspacio = r.espacio
            INNER JOIN escuela esc ON esc.IdEscuela = e.Escuela
            INNER JOIN facultad fac ON fac.IdFacultad = esc.IdFacultad
            INNER JOIN bloqueshorarios bh ON bh.IdBloque = r.bloque
            INNER JOIN cursos c ON c.IdCurso = r.curso
            LEFT JOIN (
                SELECT g1.*
                FROM reserva_gestion g1
                INNER JOIN (
                    SELECT IdReserva, MAX(FechaGestion) AS FechaGestion
                    FROM reserva_gestion
                    GROUP BY IdReserva
                ) g2 ON g1.IdReserva = g2.IdReserva AND g1.FechaGestion = g2.FechaGestion
            ) rg ON rg.IdReserva = r.IdReserva
            LEFT JOIN usuario ug ON ug.IdUsuario = rg.UsuarioGestion
            WHERE (:estado IS NULL OR r.Estado = :estado)
              AND (:tipoEspacio IS NULL OR e.Tipo = :tipoEspacio)
              AND (:facultadId IS NULL OR fac.IdFacultad = :facultadId)
              AND (:escuelaId IS NULL OR esc.IdEscuela = :escuelaId)
              AND (:fechaReserva IS NULL OR r.fechaReserva = :fechaReserva)
              AND (
                    :search IS NULL OR
                    LOWER(e.Nombre) LIKE LOWER(CONCAT('%', :search, '%')) OR
                    LOWER(e.Codigo) LIKE LOWER(CONCAT('%', :search, '%')) OR
                    LOWER(CONCAT(u.Nombre, ' ', u.Apellido)) LIKE LOWER(CONCAT('%', :search, '%'))
              )
            ORDER BY r.fechaSolicitud DESC, r.IdReserva DESC
            """, nativeQuery = true)
    List<AdminReservaProjection> buscarReservasParaAdmin(
            @Param("estado") String estado,
            @Param("tipoEspacio") String tipoEspacio,
            @Param("facultadId") Integer facultadId,
            @Param("escuelaId") Integer escuelaId,
            @Param("fechaReserva") LocalDate fechaReserva,
            @Param("search") String search
    );

    @Query(value = """
            SELECT
                r.IdReserva AS reserva_id,
                r.Estado AS estado,
                r.fechaReserva AS fecha_reserva,
                r.fechaSolicitud AS fecha_solicitud,
                r.DescripcionUso AS descripcion_uso,
                r.CantidadEstudiantes AS cantidad_estudiantes,
                e.Codigo AS espacio_codigo,
                e.Nombre AS espacio_nombre,
                e.Tipo AS espacio_tipo,
                esc.IdEscuela AS escuela_id,
                esc.Nombre AS escuela_nombre,
                fac.IdFacultad AS facultad_id,
                fac.Nombre AS facultad_nombre,
                bh.Nombre AS bloque_nombre,
                bh.HoraInicio AS hora_inicio,
                bh.HoraFinal AS hora_fin,
                c.Nombre AS curso_nombre,
                u.IdUsuario AS solicitante_id,
                u.Nombre AS solicitante_nombre,
                u.Apellido AS solicitante_apellido,
                ua.CorreoU AS solicitante_correo,
                rg.Accion AS ultima_accion,
                rg.Motivo AS motivo,
                rg.Comentarios AS comentarios,
                rg.FechaGestion AS fecha_gestion,
                ug.IdUsuario AS gestor_id,
                ug.Nombre AS gestor_nombre,
                ug.Apellido AS gestor_apellido
            FROM reserva r
            INNER JOIN usuario u ON u.IdUsuario = r.usuario
            LEFT JOIN usuario_auth ua ON ua.IdUsuario = u.IdUsuario
            INNER JOIN espacio e ON e.IdEspacio = r.espacio
            INNER JOIN escuela esc ON esc.IdEscuela = e.Escuela
            INNER JOIN facultad fac ON fac.IdFacultad = esc.IdFacultad
            INNER JOIN bloqueshorarios bh ON bh.IdBloque = r.bloque
            INNER JOIN cursos c ON c.IdCurso = r.curso
            LEFT JOIN (
                SELECT g1.*
                FROM reserva_gestion g1
                INNER JOIN (
                    SELECT IdReserva, MAX(FechaGestion) AS FechaGestion
                    FROM reserva_gestion
                    GROUP BY IdReserva
                ) g2 ON g1.IdReserva = g2.IdReserva AND g1.FechaGestion = g2.FechaGestion
            ) rg ON rg.IdReserva = r.IdReserva
            LEFT JOIN usuario ug ON ug.IdUsuario = rg.UsuarioGestion
            WHERE r.IdReserva = :id
            """, nativeQuery = true)
    AdminReservaProjection buscarReservaPorId(@Param("id") Integer id);

    @Query(value = """
            SELECT
                r.Estado AS estado,
                COUNT(*) AS total
            FROM reserva r
            INNER JOIN espacio e ON e.IdEspacio = r.espacio
            INNER JOIN escuela esc ON esc.IdEscuela = e.Escuela
            INNER JOIN facultad fac ON fac.IdFacultad = esc.IdFacultad
            WHERE (:tipoEspacio IS NULL OR e.Tipo = :tipoEspacio)
              AND (:facultadId IS NULL OR fac.IdFacultad = :facultadId)
              AND (:escuelaId IS NULL OR esc.IdEscuela = :escuelaId)
              AND (:fechaReserva IS NULL OR r.fechaReserva = :fechaReserva)
              AND (
                    :search IS NULL OR
                    LOWER(e.Nombre) LIKE LOWER(CONCAT('%', :search, '%')) OR
                    LOWER(e.Codigo) LIKE LOWER(CONCAT('%', :search, '%')) OR
                    LOWER((SELECT CONCAT(u.Nombre, ' ', u.Apellido) FROM usuario u WHERE u.IdUsuario = r.usuario)) LIKE LOWER(CONCAT('%', :search, '%'))
              )
            GROUP BY r.Estado
            """, nativeQuery = true)
    List<AdminReservaEstadoProjection> obtenerResumenEstados(
            @Param("tipoEspacio") String tipoEspacio,
            @Param("facultadId") Integer facultadId,
            @Param("escuelaId") Integer escuelaId,
            @Param("fechaReserva") LocalDate fechaReserva,
            @Param("search") String search
    );
}