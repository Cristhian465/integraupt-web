package com.BackendReportes.controlador;

import com.BackendReportes.dto.EstadisticasGeneralesResponse;
import com.BackendReportes.dto.UsoEspacioResponse;
import com.BackendReportes.dto.ReservasMesResponse;
import com.BackendReportes.servicio.ReportesServicio;
import com.itextpdf.kernel.pdf.PdfDocument;
import com.itextpdf.kernel.pdf.PdfWriter;
import com.itextpdf.layout.Document;
import com.itextpdf.layout.element.Paragraph;
import com.itextpdf.layout.element.Table;
import com.itextpdf.layout.properties.TextAlignment;
import com.itextpdf.layout.properties.UnitValue;
import org.apache.poi.ss.usermodel.*;
import org.apache.poi.xssf.usermodel.XSSFWorkbook;
import org.springframework.http.HttpHeaders;
import org.springframework.http.MediaType;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.time.LocalDateTime;
import java.time.format.DateTimeFormatter;
import java.util.List;

@RestController
@RequestMapping("/api/reportes")
public class ReportesControlador {

    private final ReportesServicio reportesServicio;

    public ReportesControlador(ReportesServicio reportesServicio) {
        this.reportesServicio = reportesServicio;
    }

    @GetMapping("/estadisticas-generales")
    public ResponseEntity<EstadisticasGeneralesResponse> obtenerEstadisticasGenerales() {
        EstadisticasGeneralesResponse estadisticas = reportesServicio.obtenerEstadisticasGenerales();
        return ResponseEntity.ok(estadisticas);
    }

    @GetMapping("/uso-espacios")
    public ResponseEntity<List<UsoEspacioResponse>> obtenerUsoEspacios() {
        List<UsoEspacioResponse> usoEspacios = reportesServicio.obtenerUsoEspacios();
        return ResponseEntity.ok(usoEspacios);
    }

    @GetMapping("/reservas-mes")
    public ResponseEntity<List<ReservasMesResponse>> obtenerReservasPorMes() {
        List<ReservasMesResponse> reservasPorMes = reportesServicio.obtenerReservasPorMes();
        return ResponseEntity.ok(reservasPorMes);
    }

    @GetMapping("/exportacion/pdf")
    public ResponseEntity<byte[]> exportarPDF() {
        try {
            // Obtener datos para el reporte
            EstadisticasGeneralesResponse estadisticas = reportesServicio.obtenerEstadisticasGenerales();
            List<UsoEspacioResponse> usoEspacios = reportesServicio.obtenerUsoEspacios();
            List<ReservasMesResponse> reservasPorMes = reportesServicio.obtenerReservasPorMes();
            
            // Generar PDF real
            byte[] pdfBytes = generarPDF(estadisticas, usoEspacios, reservasPorMes);
            
            String timestamp = LocalDateTime.now().format(DateTimeFormatter.ofPattern("yyyyMMdd_HHmmss"));
            String filename = "reporte_estadisticas_" + timestamp + ".pdf";
            
            return ResponseEntity.ok()
                    .header(HttpHeaders.CONTENT_DISPOSITION, "attachment; filename=\"" + filename + "\"")
                    .contentType(MediaType.APPLICATION_PDF)
                    .body(pdfBytes);
                    
        } catch (Exception e) {
            e.printStackTrace();
            return ResponseEntity.internalServerError().build();
        }
    }

    @GetMapping("/exportacion/excel")
    public ResponseEntity<byte[]> exportarExcel() {
        try {
            // Obtener datos para el reporte
            EstadisticasGeneralesResponse estadisticas = reportesServicio.obtenerEstadisticasGenerales();
            List<UsoEspacioResponse> usoEspacios = reportesServicio.obtenerUsoEspacios();
            List<ReservasMesResponse> reservasPorMes = reportesServicio.obtenerReservasPorMes();
            
            // Generar Excel real
            byte[] excelBytes = generarExcel(estadisticas, usoEspacios, reservasPorMes);
            
            String timestamp = LocalDateTime.now().format(DateTimeFormatter.ofPattern("yyyyMMdd_HHmmss"));
            String filename = "reporte_estadisticas_" + timestamp + ".xlsx";
            
            return ResponseEntity.ok()
                    .header(HttpHeaders.CONTENT_DISPOSITION, "attachment; filename=\"" + filename + "\"")
                    .contentType(MediaType.parseMediaType("application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"))
                    .body(excelBytes);
                    
        } catch (Exception e) {
            e.printStackTrace();
            return ResponseEntity.internalServerError().build();
        }
    }

    private byte[] generarPDF(EstadisticasGeneralesResponse estadisticas, 
                            List<UsoEspacioResponse> usoEspacios, 
                            List<ReservasMesResponse> reservasPorMes) throws IOException {
        
        ByteArrayOutputStream baos = new ByteArrayOutputStream();
        PdfWriter writer = new PdfWriter(baos);
        PdfDocument pdf = new PdfDocument(writer);
        Document document = new Document(pdf);

        // Título
        Paragraph titulo = new Paragraph("REPORTE DE ESTADÍSTICAS - SISTEMA DE RESERVAS")
                .setTextAlignment(TextAlignment.CENTER)
                .setBold()
                .setFontSize(16);
        document.add(titulo);

        // Fecha de generación
        String fechaGeneracion = LocalDateTime.now().format(DateTimeFormatter.ofPattern("dd/MM/yyyy HH:mm"));
        Paragraph fecha = new Paragraph("Generado: " + fechaGeneracion)
                .setTextAlignment(TextAlignment.CENTER)
                .setFontSize(10)
                .setItalic();
        document.add(fecha);

        document.add(new Paragraph("\n"));

        // Estadísticas Generales
        Paragraph statsTitulo = new Paragraph("ESTADÍSTICAS GENERALES")
                .setBold()
                .setFontSize(12);
        document.add(statsTitulo);

        document.add(new Paragraph("Total Estudiantes: " + estadisticas.getTotalEstudiantes()));
        document.add(new Paragraph("Total Docentes: " + estadisticas.getTotalDocentes()));
        document.add(new Paragraph("Reservas Activas: " + estadisticas.getReservasActivas()));
        document.add(new Paragraph("Tasa de Uso: " + estadisticas.getTasaUso() + "%"));
        document.add(new Paragraph("Variación: " + estadisticas.getVariacionReservas()));

        document.add(new Paragraph("\n"));

        // Uso de Espacios
        Paragraph usoEspaciosTitulo = new Paragraph("USO DE ESPACIOS (Top 5)")
                .setBold()
                .setFontSize(12);
        document.add(usoEspaciosTitulo);

        // Crear tabla para uso de espacios
        Table tablaUsoEspacios = new Table(UnitValue.createPercentArray(new float[]{3, 4, 2, 2}));
        tablaUsoEspacios.addHeaderCell("Código");
        tablaUsoEspacios.addHeaderCell("Nombre");
        tablaUsoEspacios.addHeaderCell("Reservas");
        tablaUsoEspacios.addHeaderCell("Uso %");

        for (int i = 0; i < Math.min(usoEspacios.size(), 5); i++) {
            UsoEspacioResponse espacio = usoEspacios.get(i);
            tablaUsoEspacios.addCell(espacio.getCodigoEspacio());
            tablaUsoEspacios.addCell(espacio.getNombreEspacio());
            tablaUsoEspacios.addCell(String.valueOf(espacio.getTotalReservas()));
            tablaUsoEspacios.addCell(String.format("%.1f%%", espacio.getPorcentajeUso()));
        }

        document.add(tablaUsoEspacios);
        document.add(new Paragraph("\n"));

        // Reservas por Mes
        Paragraph reservasMesTitulo = new Paragraph("RESERVAS POR MES")
                .setBold()
                .setFontSize(12);
        document.add(reservasMesTitulo);

        Table tablaReservasMes = new Table(UnitValue.createPercentArray(new float[]{3, 2}));
        tablaReservasMes.addHeaderCell("Mes/Año");
        tablaReservasMes.addHeaderCell("Total Reservas");

        for (ReservasMesResponse mes : reservasPorMes) {
            tablaReservasMes.addCell(mes.getMes() + " " + mes.getAnio());
            tablaReservasMes.addCell(String.valueOf(mes.getTotalReservas()));
        }

        document.add(tablaReservasMes);

        document.close();
        return baos.toByteArray();
    }

    private byte[] generarExcel(EstadisticasGeneralesResponse estadisticas,
                              List<UsoEspacioResponse> usoEspacios,
                              List<ReservasMesResponse> reservasPorMes) throws IOException {
        
        try (Workbook workbook = new XSSFWorkbook(); ByteArrayOutputStream baos = new ByteArrayOutputStream()) {
            
            // Estilo para encabezados
            CellStyle headerStyle = workbook.createCellStyle();
            Font headerFont = workbook.createFont();
            headerFont.setBold(true);
            headerStyle.setFont(headerFont);
            headerStyle.setFillForegroundColor(IndexedColors.GREY_25_PERCENT.getIndex());
            headerStyle.setFillPattern(FillPatternType.SOLID_FOREGROUND);

            // Hoja 1: Estadísticas Generales
            Sheet statsSheet = workbook.createSheet("Estadísticas Generales");
            
            // Título
            Row tituloRow = statsSheet.createRow(0);
            Cell tituloCell = tituloRow.createCell(0);
            tituloCell.setCellValue("REPORTE DE ESTADÍSTICAS - SISTEMA DE RESERVAS");
            
            Row fechaRow = statsSheet.createRow(1);
            Cell fechaCell = fechaRow.createCell(0);
            fechaCell.setCellValue("Generado: " + LocalDateTime.now().format(DateTimeFormatter.ofPattern("dd/MM/yyyy HH:mm")));

            // Datos estadísticas
            int rowNum = 3;
            String[] statsLabels = {"Total Estudiantes", "Total Docentes", "Reservas Activas", "Tasa de Uso", "Variación"};
            Object[] statsValues = {
                estadisticas.getTotalEstudiantes(),
                estadisticas.getTotalDocentes(),
                estadisticas.getReservasActivas(),
                estadisticas.getTasaUso() + "%",
                estadisticas.getVariacionReservas()
            };

            for (int i = 0; i < statsLabels.length; i++) {
                Row row = statsSheet.createRow(rowNum++);
                row.createCell(0).setCellValue(statsLabels[i]);
                row.createCell(1).setCellValue(statsValues[i].toString());
            }

            // Hoja 2: Uso de Espacios
            Sheet usoSheet = workbook.createSheet("Uso de Espacios");
            Row usoHeaderRow = usoSheet.createRow(0);
            String[] usoHeaders = {"Código", "Nombre", "Tipo", "Total Reservas", "Porcentaje Uso"};
            
            for (int i = 0; i < usoHeaders.length; i++) {
                Cell cell = usoHeaderRow.createCell(i);
                cell.setCellValue(usoHeaders[i]);
                cell.setCellStyle(headerStyle);
            }

            rowNum = 1;
            for (UsoEspacioResponse espacio : usoEspacios) {
                Row row = usoSheet.createRow(rowNum++);
                row.createCell(0).setCellValue(espacio.getCodigoEspacio());
                row.createCell(1).setCellValue(espacio.getNombreEspacio());
                row.createCell(2).setCellValue(espacio.getTipoEspacio());
                row.createCell(3).setCellValue(espacio.getTotalReservas());
                row.createCell(4).setCellValue(espacio.getPorcentajeUso());
            }

            // Hoja 3: Reservas por Mes
            Sheet mesSheet = workbook.createSheet("Reservas por Mes");
            Row mesHeaderRow = mesSheet.createRow(0);
            String[] mesHeaders = {"Mes", "Año", "Total Reservas"};
            
            for (int i = 0; i < mesHeaders.length; i++) {
                Cell cell = mesHeaderRow.createCell(i);
                cell.setCellValue(mesHeaders[i]);
                cell.setCellStyle(headerStyle);
            }

            rowNum = 1;
            for (ReservasMesResponse mes : reservasPorMes) {
                Row row = mesSheet.createRow(rowNum++);
                row.createCell(0).setCellValue(mes.getMes());
                row.createCell(1).setCellValue(mes.getAnio());
                row.createCell(2).setCellValue(mes.getTotalReservas());
            }

            // Autoajustar columnas
            for (int i = 0; i < workbook.getNumberOfSheets(); i++) {
                Sheet sheet = workbook.getSheetAt(i);
                for (int j = 0; j < sheet.getRow(0).getLastCellNum(); j++) {
                    sheet.autoSizeColumn(j);
                }
            }

            workbook.write(baos);
            return baos.toByteArray();
        }
    }
}