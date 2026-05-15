package com.auditoria.dto;

import java.time.LocalDateTime;
import java.util.Optional;

public record AuditoriaReservaFiltro(
        Integer reservaId,
        String estado,
        String terminoUsuario,
        LocalDateTime fechaInicio,
        LocalDateTime fechaFin
) {

    public Optional<Integer> reservaIdOptional() {
        return Optional.ofNullable(reservaId);
    }

    public Optional<String> estadoOptional() {
        return Optional.ofNullable(estado).map(String::trim).filter(value -> !value.isEmpty());
    }

    public Optional<String> terminoUsuarioOptional() {
        return Optional.ofNullable(terminoUsuario).map(String::trim).filter(value -> !value.isEmpty());
    }

    public Optional<LocalDateTime> fechaInicioOptional() {
        return Optional.ofNullable(fechaInicio);
    }

    public Optional<LocalDateTime> fechaFinOptional() {
        return Optional.ofNullable(fechaFin);
    }
}
