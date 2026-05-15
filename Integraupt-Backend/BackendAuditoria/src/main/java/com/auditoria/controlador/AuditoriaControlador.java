package com.auditoria.controlador;

import com.auditoria.dto.AuditoriaReservaFiltro;
import com.auditoria.dto.AuditoriaReservaResponse;
import com.auditoria.interfaces.IAuditoriaReservaServicio;
import com.auditoria.servicio.AuditoriaExportServicio;
import jakarta.validation.constraints.Min;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.LocalTime;
import java.time.format.DateTimeFormatter;
import java.time.format.DateTimeParseException;
import java.util.List;
import org.springframework.http.HttpHeaders;
import org.springframework.http.HttpStatus;
import org.springframework.http.MediaType;
import org.springframework.http.ResponseEntity;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;
import org.springframework.web.server.ResponseStatusException;

@RestController
@Validated
@RequestMapping("/api/auditorias")
public class AuditoriaControlador {

    private static final DateTimeFormatter ISO_DATE_TIME = DateTimeFormatter.ISO_DATE_TIME;

    private final IAuditoriaReservaServicio auditoriaReservaServicio;
    private final AuditoriaExportServicio auditoriaExportServicio;

    public AuditoriaControlador(
            IAuditoriaReservaServicio auditoriaReservaServicio,
            AuditoriaExportServicio auditoriaExportServicio
    ) {
        this.auditoriaReservaServicio = auditoriaReservaServicio;
        this.auditoriaExportServicio = auditoriaExportServicio;
    }

    @GetMapping
    public List<AuditoriaReservaResponse> listar(
            @RequestParam(name = "reservaId", required = false) Integer reservaId,
            @RequestParam(name = "estado", required = false) String estado,
            @RequestParam(name = "usuario", required = false) String terminoUsuario,
            @RequestParam(name = "fechaInicio", required = false) String fechaInicio,
            @RequestParam(name = "fechaFin", required = false) String fechaFin
    ) {
        return auditoriaReservaServicio.buscar(
                construirFiltro(reservaId, estado, terminoUsuario, fechaInicio, fechaFin)
        );
    }

    @GetMapping("/{id}")
    public AuditoriaReservaResponse detalle(@PathVariable @Min(1) Integer id) {
        return auditoriaReservaServicio.buscarPorId(id);
    }

    @GetMapping("/reserva/{reservaId}")
    public List<AuditoriaReservaResponse> porReserva(@PathVariable @Min(1) Integer reservaId) {
        return auditoriaReservaServicio.listarPorReserva(reservaId);
    }

    @GetMapping("/exportacion/pdf")
    public ResponseEntity<byte[]> exportarPdf(
            @RequestParam(name = "reservaId", required = false) Integer reservaId,
            @RequestParam(name = "estado", required = false) String estado,
            @RequestParam(name = "usuario", required = false) String terminoUsuario,
            @RequestParam(name = "fechaInicio", required = false) String fechaInicio,
            @RequestParam(name = "fechaFin", required = false) String fechaFin
    ) {
        AuditoriaReservaFiltro filtro = construirFiltro(reservaId, estado, terminoUsuario, fechaInicio, fechaFin);
        byte[] data = auditoriaExportServicio.generarReportePdf(filtro);
        String filename = "reporte_auditoria_" + System.currentTimeMillis() + ".pdf";
        return ResponseEntity.ok()
                .header(HttpHeaders.CONTENT_DISPOSITION, "attachment; filename=\"" + filename + "\"")
                .contentType(MediaType.APPLICATION_PDF)
                .body(data);
    }

    @GetMapping("/exportacion/excel")
    public ResponseEntity<byte[]> exportarExcel(
            @RequestParam(name = "reservaId", required = false) Integer reservaId,
            @RequestParam(name = "estado", required = false) String estado,
            @RequestParam(name = "usuario", required = false) String terminoUsuario,
            @RequestParam(name = "fechaInicio", required = false) String fechaInicio,
            @RequestParam(name = "fechaFin", required = false) String fechaFin
    ) {
        AuditoriaReservaFiltro filtro = construirFiltro(reservaId, estado, terminoUsuario, fechaInicio, fechaFin);
        byte[] data = auditoriaExportServicio.generarReporteExcel(filtro);
        String filename = "reporte_auditoria_" + System.currentTimeMillis() + ".xlsx";
        return ResponseEntity.ok()
                .header(HttpHeaders.CONTENT_DISPOSITION, "attachment; filename=\"" + filename + "\"")
                .contentType(MediaType.parseMediaType("application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"))
                .body(data);
    }

    private LocalDateTime parseFecha(String valor, boolean endOfDay) {
        if (valor == null || valor.isBlank()) {
            return null;
        }

        try {
            if (valor.length() == 10) {
                LocalDate fecha = LocalDate.parse(valor);
                return endOfDay ? fecha.atTime(LocalTime.MAX) : fecha.atStartOfDay();
            }
            return LocalDateTime.parse(valor, ISO_DATE_TIME);
        } catch (DateTimeParseException ex) {
            throw new ResponseStatusException(
                    HttpStatus.BAD_REQUEST,
                    "Formato de fecha no valido. Usa ISO-8601 (ej. 2025-11-11T15:30:00)."
            );
        }
    }

    private AuditoriaReservaFiltro construirFiltro(
            Integer reservaId,
            String estado,
            String terminoUsuario,
            String fechaInicio,
            String fechaFin
    ) {
        return new AuditoriaReservaFiltro(
                reservaId,
                estado,
                terminoUsuario,
                parseFecha(fechaInicio, false),
                parseFecha(fechaFin, true)
        );
    }
}
