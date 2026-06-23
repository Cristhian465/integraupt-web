package com.auditoria.dto;

import java.time.LocalDate;
import java.time.LocalDateTime;

public record AuditoriaReservaResponse(
        Integer idAudit,
        Integer idReserva,
        String estadoAnterior,
        String estadoNuevo,
        LocalDateTime fechaCambio,
        Integer usuarioCambioId,
        String usuarioCambioNombre,
        String usuarioCambioDocumento,
        String estadoReservaActual,
        String descripcionUso,
        LocalDate fechaReserva,
        String codigoEspacio,
        String nombreEspacio
) {
}
