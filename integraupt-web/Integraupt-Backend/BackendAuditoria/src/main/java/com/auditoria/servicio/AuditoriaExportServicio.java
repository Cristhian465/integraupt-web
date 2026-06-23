package com.auditoria.servicio;

import com.auditoria.dto.AuditoriaReservaFiltro;
import com.auditoria.dto.AuditoriaReservaResponse;
import com.auditoria.interfaces.IAuditoriaReservaServicio;
import com.itextpdf.kernel.pdf.PdfDocument;
import com.itextpdf.kernel.pdf.PdfWriter;
import com.itextpdf.layout.Document;
import com.itextpdf.layout.element.Cell;
import com.itextpdf.layout.element.Paragraph;
import com.itextpdf.layout.element.Table;
import com.itextpdf.layout.properties.TextAlignment;
import com.itextpdf.layout.properties.UnitValue;
import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.time.format.DateTimeFormatter;
import java.util.List;
import org.apache.poi.ss.usermodel.CellStyle;
import org.apache.poi.ss.usermodel.Row;
import org.apache.poi.ss.usermodel.Sheet;
import org.apache.poi.ss.usermodel.Workbook;
import org.apache.poi.xssf.usermodel.XSSFWorkbook;
import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Service;
import org.springframework.web.server.ResponseStatusException;

@Service
public class AuditoriaExportServicio {

    private static final DateTimeFormatter DATE_TIME_FORMATTER = DateTimeFormatter.ofPattern("dd/MM/yyyy HH:mm");

    private final IAuditoriaReservaServicio auditoriaReservaServicio;

    public AuditoriaExportServicio(IAuditoriaReservaServicio auditoriaReservaServicio) {
        this.auditoriaReservaServicio = auditoriaReservaServicio;
    }

    public byte[] generarReportePdf(AuditoriaReservaFiltro filtro) {
        List<AuditoriaReservaResponse> registros = auditoriaReservaServicio.buscar(filtro);
        try (ByteArrayOutputStream outputStream = new ByteArrayOutputStream()) {
            PdfWriter writer = new PdfWriter(outputStream);
            PdfDocument pdf = new PdfDocument(writer);
            Document document = new Document(pdf);

            document.add(new Paragraph("Reporte de Auditoria de Reservas")
                    .setBold()
                    .setFontSize(14)
                    .setTextAlignment(TextAlignment.CENTER));

            document.add(new Paragraph("Total de eventos: " + registros.size())
                    .setMarginBottom(10));

            Table table = new Table(UnitValue.createPercentArray(new float[]{1, 1.2f, 1.5f, 1.5f, 1.5f, 1.6f}))
                    .useAllAvailableWidth();

            addHeaderCell(table, "# Audit");
            addHeaderCell(table, "Reserva");
            addHeaderCell(table, "Cambio");
            addHeaderCell(table, "Usuario");
            addHeaderCell(table, "Espacio");
            addHeaderCell(table, "Fecha");

            for (AuditoriaReservaResponse registro : registros) {
                table.addCell(createBodyCell(String.valueOf(registro.idAudit())));
                table.addCell(createBodyCell("#" + registro.idReserva()));
                table.addCell(createBodyCell(registro.estadoAnterior() + " -> " + registro.estadoNuevo()));
                table.addCell(createBodyCell(
                        registro.usuarioCambioNombre() != null ? registro.usuarioCambioNombre() : "Sin usuario"));
                table.addCell(createBodyCell(
                        registro.nombreEspacio() != null ? registro.nombreEspacio() : "Sin espacio"));
                table.addCell(createBodyCell(formatFecha(registro.fechaCambio())));
            }

            document.add(table);
            document.close();
            return outputStream.toByteArray();
        } catch (IOException ex) {
            throw new ResponseStatusException(HttpStatus.INTERNAL_SERVER_ERROR, "No se pudo generar el PDF.", ex);
        }
    }

    public byte[] generarReporteExcel(AuditoriaReservaFiltro filtro) {
        List<AuditoriaReservaResponse> registros = auditoriaReservaServicio.buscar(filtro);
        try (Workbook workbook = new XSSFWorkbook(); ByteArrayOutputStream outputStream = new ByteArrayOutputStream()) {
            Sheet sheet = workbook.createSheet("Auditorias");

            CellStyle headerStyle = workbook.createCellStyle();
            headerStyle.setWrapText(true);

            Row header = sheet.createRow(0);
            header.createCell(0).setCellValue("# Audit");
            header.createCell(1).setCellValue("Reserva");
            header.createCell(2).setCellValue("Cambio");
            header.createCell(3).setCellValue("Usuario");
            header.createCell(4).setCellValue("Espacio");
            header.createCell(5).setCellValue("Fecha");

            for (int i = 0; i <= 5; i++) {
                header.getCell(i).setCellStyle(headerStyle);
            }

            int rowIndex = 1;
            for (AuditoriaReservaResponse registro : registros) {
                Row row = sheet.createRow(rowIndex++);
                row.createCell(0).setCellValue(registro.idAudit());
                row.createCell(1).setCellValue(registro.idReserva());
                row.createCell(2).setCellValue(registro.estadoAnterior() + " -> " + registro.estadoNuevo());
                row.createCell(3).setCellValue(
                        registro.usuarioCambioNombre() != null ? registro.usuarioCambioNombre() : "Sin usuario");
                row.createCell(4).setCellValue(
                        registro.nombreEspacio() != null ? registro.nombreEspacio() : "Sin espacio");
                row.createCell(5).setCellValue(formatFecha(registro.fechaCambio()));
            }

            for (int i = 0; i <= 5; i++) {
                sheet.autoSizeColumn(i);
            }

            workbook.write(outputStream);
            return outputStream.toByteArray();
        } catch (IOException ex) {
            throw new ResponseStatusException(HttpStatus.INTERNAL_SERVER_ERROR, "No se pudo generar el Excel.", ex);
        }
    }

    private void addHeaderCell(Table table, String value) {
        table.addHeaderCell(new Cell()
                .add(new Paragraph(value))
                .setBold()
                .setTextAlignment(TextAlignment.CENTER));
    }

    private Cell createBodyCell(String value) {
        return new Cell()
                .add(new Paragraph(value != null ? value : "-"))
                .setTextAlignment(TextAlignment.LEFT);
    }

    private String formatFecha(java.time.LocalDateTime fecha) {
        if (fecha == null) {
            return "-";
        }
        return fecha.format(DATE_TIME_FORMATTER);
    }
}
