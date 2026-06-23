package com.horariocurso.repositorio;

import com.horariocurso.dto.catalogo.BloqueCatalogoDto;
import com.horariocurso.dto.catalogo.CursoCatalogoDto;
import com.horariocurso.dto.catalogo.DocenteCatalogoDto;
import com.horariocurso.dto.catalogo.EspacioCatalogoDto;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.List;
import org.springframework.jdbc.core.JdbcTemplate;
import org.springframework.jdbc.core.RowMapper;
import org.springframework.stereotype.Repository;

@Repository
public class CatalogoRepositorio {

    private final JdbcTemplate jdbcTemplate;

    public CatalogoRepositorio(JdbcTemplate jdbcTemplate) {
        this.jdbcTemplate = jdbcTemplate;
    }

    public List<CursoCatalogoDto> listarCursos() {
        String sql = """
                SELECT IdCurso AS id, Nombre AS nombre
                FROM cursos
                WHERE Estado = 1
                ORDER BY Nombre ASC
                """;
        return jdbcTemplate.query(sql, (rs, rowNum) -> new CursoCatalogoDto(
                rs.getInt("id"),
                rs.getString("nombre")
        ));
    }

    public List<DocenteCatalogoDto> listarDocentes() {
        String sql = """
                SELECT u.IdUsuario  AS id,
                       u.Nombre     AS nombres,
                       u.Apellido   AS apellidos,
                       d.CodigoDocente AS codigo
                FROM usuario u
                LEFT JOIN docente d ON d.IdUsuario = u.IdUsuario
                WHERE u.Estado = 1
                  AND (u.Rol = 3 OR d.IdDocente IS NOT NULL)
                ORDER BY u.Nombre ASC, u.Apellido ASC
                """;
        return jdbcTemplate.query(sql, (rs, rowNum) -> new DocenteCatalogoDto(
                rs.getInt("id"),
                rs.getString("nombres"),
                rs.getString("apellidos"),
                rs.getString("codigo")
        ));
    }

    public List<EspacioCatalogoDto> listarEspacios() {
        String sql = """
                SELECT IdEspacio AS id,
                       Codigo    AS codigo,
                       Nombre    AS nombre,
                       Tipo      AS tipo,
                       Capacidad AS capacidad
                FROM espacio
                WHERE Estado = 1
                ORDER BY Nombre ASC
                """;
        return jdbcTemplate.query(sql, (rs, rowNum) -> new EspacioCatalogoDto(
                rs.getInt("id"),
                rs.getString("codigo"),
                rs.getString("nombre"),
                rs.getString("tipo"),
                rs.getInt("capacidad")
        ));
    }

    public List<BloqueCatalogoDto> listarBloques() {
        String sql = """
                SELECT IdBloque AS id,
                       Nombre   AS nombre,
                       DATE_FORMAT(HoraInicio, '%H:%i') AS horaInicio,
                       DATE_FORMAT(HoraFinal,  '%H:%i') AS horaFinal
                FROM bloqueshorarios
                ORDER BY Orden ASC
                """;
        RowMapper<BloqueCatalogoDto> mapper = (ResultSet rs, int rowNum) -> new BloqueCatalogoDto(
                rs.getInt("id"),
                rs.getString("nombre"),
                rs.getString("horaInicio"),
                rs.getString("horaFinal")
        );
        return jdbcTemplate.query(sql, mapper);
    }

    public boolean existeCurso(Integer id) {
        return existe("SELECT COUNT(1) FROM cursos WHERE IdCurso = ?", id);
    }

    public boolean existeDocente(Integer id) {
        return existe("SELECT COUNT(1) FROM usuario WHERE IdUsuario = ?", id);
    }

    public boolean existeEspacio(Integer id) {
        return existe("SELECT COUNT(1) FROM espacio WHERE IdEspacio = ?", id);
    }

    public boolean existeBloque(Integer id) {
        return existe("SELECT COUNT(1) FROM bloqueshorarios WHERE IdBloque = ?", id);
    }

    private boolean existe(String sql, Integer id) {
        if (id == null) {
            return false;
        }
        Integer count = jdbcTemplate.queryForObject(sql, Integer.class, id);
        return count != null && count > 0;
    }
}
