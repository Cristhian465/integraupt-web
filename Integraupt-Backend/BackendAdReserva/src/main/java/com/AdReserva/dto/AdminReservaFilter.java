package com.AdReserva.dto;

import java.time.LocalDate;
import java.util.Optional;

public record AdminReservaFilter(
        String estado,
        String tipoEspacio,
        Integer facultadId,
        Integer escuelaId,
        LocalDate fechaReserva,
        String search,
        Integer usuarioId,
        String rol
) {
    public Optional<String> estadoOpt() {
        return Optional.ofNullable(estado).filter(value -> !value.isBlank());
    }

    public Optional<String> tipoEspacioOpt() {
        return Optional.ofNullable(tipoEspacio).filter(value -> !value.isBlank());
    }

    public Optional<Integer> facultadIdOpt() {
        return Optional.ofNullable(facultadId);
    }

    public Optional<Integer> escuelaIdOpt() {
        return Optional.ofNullable(escuelaId);
    }

    public Optional<LocalDate> fechaReservaOpt() {
        return Optional.ofNullable(fechaReserva);
    }

    public Optional<String> searchOpt() {
        return Optional.ofNullable(search).filter(value -> !value.isBlank());
    }

    public Optional<Integer> usuarioIdOpt() {
        return Optional.ofNullable(usuarioId);
    }

    public Optional<String> rolOpt() {
        return Optional.ofNullable(rol)
                .map(String::trim)
                .filter(value -> !value.isEmpty());
    }
}