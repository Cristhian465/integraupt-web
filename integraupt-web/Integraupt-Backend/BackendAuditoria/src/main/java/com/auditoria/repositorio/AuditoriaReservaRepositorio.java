package com.auditoria.repositorio;

import com.auditoria.modelo.AuditoriaReserva;
import com.auditoria.repositorio.proyecciones.AuditoriaReservaResumenProjection;
import java.time.LocalDateTime;
import java.util.List;
import java.util.Optional;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;

public interface AuditoriaReservaRepositorio extends JpaRepository<AuditoriaReserva, Integer> {

    @Query(
            value = """
                    SELECT
                        a.IdAudit AS idAudit,
                        a.IdReserva AS idReserva,
                        a.EstadoAnterior AS estadoAnterior,
                        a.EstadoNuevo AS estadoNuevo,
                        a.FechaCambio AS fechaCambio,
                        a.UsuarioCambio AS usuarioCambioId,
                        CONCAT(
                            COALESCE(u.Nombre, ''),
                            CASE WHEN u.Nombre IS NOT NULL AND u.Apellido IS NOT NULL THEN ' ' ELSE '' END,
                            COALESCE(u.Apellido, '')
                        ) AS usuarioCambioNombre,
                        u.NumDoc AS usuarioCambioDocumento,
                        r.Estado AS estadoReservaActual,
                        r.DescripcionUso AS descripcionUso,
                        r.fechaReserva AS fechaReserva,
                        e.Codigo AS codigoEspacio,
                        e.Nombre AS nombreEspacio
                    FROM auditoriareserva a
                    JOIN reserva r ON r.IdReserva = a.IdReserva
                    LEFT JOIN usuario u ON u.IdUsuario = a.UsuarioCambio
                    LEFT JOIN espacio e ON e.IdEspacio = r.espacio
                    WHERE (:reservaId IS NULL OR a.IdReserva = :reservaId)
                      AND (
                        :estado IS NULL
                        OR UPPER(a.EstadoNuevo) = UPPER(:estado)
                        OR UPPER(a.EstadoAnterior) = UPPER(:estado)
                      )
                      AND (
                        :usuarioTerm IS NULL
                        OR u.Nombre LIKE CONCAT('%', :usuarioTerm, '%')
                        OR u.Apellido LIKE CONCAT('%', :usuarioTerm, '%')
                        OR u.NumDoc LIKE CONCAT('%', :usuarioTerm, '%')
                      )
                      AND (:fechaInicio IS NULL OR a.FechaCambio >= :fechaInicio)
                      AND (:fechaFin IS NULL OR a.FechaCambio <= :fechaFin)
                    ORDER BY a.FechaCambio DESC
                    """,
            nativeQuery = true
    )
    List<AuditoriaReservaResumenProjection> buscarResumenes(
            @Param("reservaId") Integer reservaId,
            @Param("estado") String estado,
            @Param("usuarioTerm") String usuarioTerm,
            @Param("fechaInicio") LocalDateTime fechaInicio,
            @Param("fechaFin") LocalDateTime fechaFin
    );

    @Query(
            value = """
                    SELECT
                        a.IdAudit AS idAudit,
                        a.IdReserva AS idReserva,
                        a.EstadoAnterior AS estadoAnterior,
                        a.EstadoNuevo AS estadoNuevo,
                        a.FechaCambio AS fechaCambio,
                        a.UsuarioCambio AS usuarioCambioId,
                        CONCAT(
                            COALESCE(u.Nombre, ''),
                            CASE WHEN u.Nombre IS NOT NULL AND u.Apellido IS NOT NULL THEN ' ' ELSE '' END,
                            COALESCE(u.Apellido, '')
                        ) AS usuarioCambioNombre,
                        u.NumDoc AS usuarioCambioDocumento,
                        r.Estado AS estadoReservaActual,
                        r.DescripcionUso AS descripcionUso,
                        r.fechaReserva AS fechaReserva,
                        e.Codigo AS codigoEspacio,
                        e.Nombre AS nombreEspacio
                    FROM auditoriareserva a
                    JOIN reserva r ON r.IdReserva = a.IdReserva
                    LEFT JOIN usuario u ON u.IdUsuario = a.UsuarioCambio
                    LEFT JOIN espacio e ON e.IdEspacio = r.espacio
                    WHERE a.IdReserva = :reservaId
                    ORDER BY a.FechaCambio DESC
                    """,
            nativeQuery = true
    )
    List<AuditoriaReservaResumenProjection> listarPorReserva(@Param("reservaId") Integer reservaId);

    @Query(
            value = """
                    SELECT
                        a.IdAudit AS idAudit,
                        a.IdReserva AS idReserva,
                        a.EstadoAnterior AS estadoAnterior,
                        a.EstadoNuevo AS estadoNuevo,
                        a.FechaCambio AS fechaCambio,
                        a.UsuarioCambio AS usuarioCambioId,
                        CONCAT(
                            COALESCE(u.Nombre, ''),
                            CASE WHEN u.Nombre IS NOT NULL AND u.Apellido IS NOT NULL THEN ' ' ELSE '' END,
                            COALESCE(u.Apellido, '')
                        ) AS usuarioCambioNombre,
                        u.NumDoc AS usuarioCambioDocumento,
                        r.Estado AS estadoReservaActual,
                        r.DescripcionUso AS descripcionUso,
                        r.fechaReserva AS fechaReserva,
                        e.Codigo AS codigoEspacio,
                        e.Nombre AS nombreEspacio
                    FROM auditoriareserva a
                    JOIN reserva r ON r.IdReserva = a.IdReserva
                    LEFT JOIN usuario u ON u.IdUsuario = a.UsuarioCambio
                    LEFT JOIN espacio e ON e.IdEspacio = r.espacio
                    WHERE a.IdAudit = :idAudit
                    """,
            nativeQuery = true
    )
    Optional<AuditoriaReservaResumenProjection> obtenerDetalle(@Param("idAudit") Integer idAudit);
}
