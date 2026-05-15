package com.BackendReportes.repositorio;

import com.BackendReportes.dto.EstadisticasGeneralesResponse;
import com.BackendReportes.dto.UsoEspacioResponse;
import com.BackendReportes.dto.ReservasMesResponse;
import org.springframework.jdbc.core.JdbcTemplate;
import org.springframework.jdbc.core.RowMapper;
import org.springframework.stereotype.Repository;

import java.sql.ResultSet;
import java.sql.SQLException;
import java.time.LocalDate;
import java.time.YearMonth;
import java.time.format.DateTimeFormatter;
import java.util.List;

@Repository
public class ReportesRepositorio {

    private final JdbcTemplate jdbcTemplate;

    public ReportesRepositorio(JdbcTemplate jdbcTemplate) {
        this.jdbcTemplate = jdbcTemplate;
    }

    public EstadisticasGeneralesResponse obtenerEstadisticasGenerales() {
        // Total estudiantes
        String sqlEstudiantes = "SELECT COUNT(*) FROM estudiante WHERE IdUsuario IN (SELECT IdUsuario FROM usuario WHERE Estado = 1)";
        Integer totalEstudiantes = jdbcTemplate.queryForObject(sqlEstudiantes, Integer.class);

        // Total docentes
        String sqlDocentes = "SELECT COUNT(*) FROM docente WHERE IdUsuario IN (SELECT IdUsuario FROM usuario WHERE Estado = 1)";
        Integer totalDocentes = jdbcTemplate.queryForObject(sqlDocentes, Integer.class);

        // Reservas activas (aprobadas y con fecha futura)
        String sqlReservasActivas = """
            SELECT COUNT(*) FROM reserva 
            WHERE Estado = 'Aprobada' 
            AND fechaReserva >= CURDATE()
            """;
        Integer reservasActivas = jdbcTemplate.queryForObject(sqlReservasActivas, Integer.class);

        // Tasa de uso (porcentaje de espacios con al menos una reserva en el último mes)
        LocalDate haceUnMes = LocalDate.now().minusMonths(1);
        String sqlEspaciosConReservas = """
            SELECT COUNT(DISTINCT espacio) FROM reserva 
            WHERE fechaReserva >= ? AND Estado = 'Aprobada'
            """;
        Integer espaciosConReservas = jdbcTemplate.queryForObject(sqlEspaciosConReservas, Integer.class, haceUnMes);

        String sqlTotalEspacios = "SELECT COUNT(*) FROM espacio WHERE Estado = 1";
        Integer totalEspacios = jdbcTemplate.queryForObject(sqlTotalEspacios, Integer.class);

        Double tasaUso = totalEspacios > 0 ? (espaciosConReservas * 100.0 / totalEspacios) : 0.0;

        // Reservas este mes
        YearMonth mesActual = YearMonth.now();
        LocalDate inicioMesActual = mesActual.atDay(1);
        LocalDate finMesActual = mesActual.atEndOfMonth();

        String sqlReservasEsteMes = """
            SELECT COUNT(*) FROM reserva 
            WHERE fechaReserva BETWEEN ? AND ? 
            AND Estado = 'Aprobada'
            """;
        Integer reservasEsteMes = jdbcTemplate.queryForObject(sqlReservasEsteMes, Integer.class, inicioMesActual, finMesActual);

        // Reservas mes anterior
        YearMonth mesAnterior = mesActual.minusMonths(1);
        LocalDate inicioMesAnterior = mesAnterior.atDay(1);
        LocalDate finMesAnterior = mesAnterior.atEndOfMonth();

        String sqlReservasMesAnterior = """
            SELECT COUNT(*) FROM reserva 
            WHERE fechaReserva BETWEEN ? AND ? 
            AND Estado = 'Aprobada'
            """;
        Integer reservasMesAnterior = jdbcTemplate.queryForObject(sqlReservasMesAnterior, Integer.class, inicioMesAnterior, finMesAnterior);

        // Variación
        String variacionReservas;
        if (reservasMesAnterior == 0) {
            variacionReservas = reservasEsteMes > 0 ? "↑ 100%" : "→ 0%";
        } else {
            double variacion = ((reservasEsteMes - reservasMesAnterior) * 100.0) / reservasMesAnterior;
            if (variacion > 0) {
                variacionReservas = String.format("↑ %.1f%%", variacion);
            } else if (variacion < 0) {
                variacionReservas = String.format("↓ %.1f%%", Math.abs(variacion));
            } else {
                variacionReservas = "→ 0%";
            }
        }

        return new EstadisticasGeneralesResponse(
            totalEstudiantes, totalDocentes, reservasActivas, 
            Math.round(tasaUso * 10.0) / 10.0, // Redondear a 1 decimal
            reservasEsteMes, reservasMesAnterior, variacionReservas
        );
    }

    public List<UsoEspacioResponse> obtenerUsoEspacios() {
        String sql = """
            SELECT 
                e.Nombre AS nombreEspacio,
                e.Codigo AS codigoEspacio,
                e.Tipo AS tipoEspacio,
                COUNT(r.IdReserva) AS totalReservas,
                ROUND((COUNT(r.IdReserva) * 100.0 / (
                    SELECT COUNT(*) FROM reserva 
                    WHERE Estado = 'Aprobada' 
                    AND fechaReserva >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
                )), 1) AS porcentajeUso
            FROM espacio e
            LEFT JOIN reserva r ON e.IdEspacio = r.espacio 
                AND r.Estado = 'Aprobada'
                AND r.fechaReserva >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
            WHERE e.Estado = 1
            GROUP BY e.IdEspacio, e.Nombre, e.Codigo, e.Tipo
            ORDER BY totalReservas DESC
            LIMIT 10
            """;

        return jdbcTemplate.query(sql, (rs, rowNum) -> new UsoEspacioResponse(
            rs.getString("nombreEspacio"),
            rs.getString("codigoEspacio"),
            rs.getString("tipoEspacio"),
            rs.getInt("totalReservas"),
            rs.getDouble("porcentajeUso")
        ));
    }

    public List<ReservasMesResponse> obtenerReservasPorMes() {
        String sql = """
            SELECT 
                DATE_FORMAT(fechaReserva, '%M') AS mes,
                YEAR(fechaReserva) AS anio,
                COUNT(*) AS totalReservas
            FROM reserva 
            WHERE Estado = 'Aprobada'
                AND fechaReserva >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY YEAR(fechaReserva), MONTH(fechaReserva), DATE_FORMAT(fechaReserva, '%M')
            ORDER BY anio DESC, MONTH(fechaReserva) DESC
            LIMIT 6
            """;

        return jdbcTemplate.query(sql, (rs, rowNum) -> new ReservasMesResponse(
            rs.getString("mes"),
            rs.getInt("anio"),
            rs.getInt("totalReservas")
        ));
    }
}