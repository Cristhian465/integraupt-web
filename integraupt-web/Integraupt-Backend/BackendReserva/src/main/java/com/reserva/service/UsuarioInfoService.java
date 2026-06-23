package com.reserva.service;

import java.util.Objects;
import java.util.Optional;
import java.util.stream.Stream;

import org.springframework.jdbc.core.JdbcTemplate;
import org.springframework.jdbc.core.ResultSetExtractor;
import org.springframework.stereotype.Service;

import com.reserva.dto.SolicitanteInfo;

@Service
public class UsuarioInfoService {

    private static final String CONSULTA_DATOS_USUARIO = """
            SELECT
                TRIM(COALESCE(u.Nombre, '')) AS nombre,
                TRIM(COALESCE(u.Apellido, '')) AS apellido,
                TRIM(COALESCE(r.Nombre, '')) AS rol,
                TRIM(COALESCE(e.Codigo, '')) AS codigo_estudiante,
                TRIM(COALESCE(d.CodigoDocente, '')) AS codigo_docente,
                TRIM(COALESCE(u.NumDoc, '')) AS numero_documento,
                u.IdUsuario AS id_usuario
            FROM usuario u
            JOIN rol r ON r.IdRol = u.Rol
            LEFT JOIN estudiante e ON e.IdUsuario = u.IdUsuario
            LEFT JOIN docente d ON d.IdUsuario = u.IdUsuario
            WHERE u.IdUsuario = ?
            """;

    private final JdbcTemplate jdbcTemplate;

    public UsuarioInfoService(JdbcTemplate jdbcTemplate) {
        this.jdbcTemplate = jdbcTemplate;
    }

    public Optional<SolicitanteInfo> obtenerSolicitante(Integer usuarioId) {
        if (usuarioId == null) {
            return Optional.empty();
        }

        return jdbcTemplate.query(
                CONSULTA_DATOS_USUARIO,
                ps -> ps.setInt(1, usuarioId),
                extraerSolicitante());
    }

    private ResultSetExtractor<Optional<SolicitanteInfo>> extraerSolicitante() {
        return rs -> {
            if (!rs.next()) {
                return Optional.empty();
            }

            String nombre = rs.getString("nombre");
            String apellido = rs.getString("apellido");
            String rol = rs.getString("rol");
            String codigoEstudiante = rs.getString("codigo_estudiante");
            String codigoDocente = rs.getString("codigo_docente");
            String numeroDocumento = rs.getString("numero_documento");
            int idUsuario = rs.getInt("id_usuario");

            String nombreCompleto = construirNombre(nombre, apellido, idUsuario);
            String codigo = seleccionarCodigo(codigoEstudiante, codigoDocente, numeroDocumento, idUsuario);
            String rolNormalizado = rol != null && !rol.isBlank() ? rol : "Usuario";

            return Optional.of(new SolicitanteInfo(nombreCompleto, codigo, rolNormalizado));
        };
    }

    private String construirNombre(String nombre, String apellido, int idUsuario) {
        String nombreCompleto = Stream.of(nombre, apellido)
                .filter(valor -> valor != null && !valor.isBlank())
                .map(String::trim)
                .reduce((parte1, parte2) -> parte1 + " " + parte2)
                .orElse("");

        if (nombreCompleto.isBlank()) {
            return "Usuario " + idUsuario;
        }

        return nombreCompleto;
    }

    private String seleccionarCodigo(String codigoEstudiante,
                                     String codigoDocente,
                                     String numeroDocumento,
                                     int idUsuario) {
        return Stream.of(codigoEstudiante, codigoDocente, numeroDocumento, String.valueOf(idUsuario))
                .map(valor -> Objects.nonNull(valor) ? valor.trim() : "")
                .filter(valor -> !valor.isBlank())
                .findFirst()
                .orElse(String.valueOf(idUsuario));
    }
}