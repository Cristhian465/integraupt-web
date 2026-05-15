package com.AdReserva.dto;

public enum ReservaGestionAccion {
    APROBAR("Aprobar", "Aprobada"),
    RECHAZAR("Rechazar", "Rechazada");

    private final String accionBd;
    private final String estadoResultado;

    ReservaGestionAccion(String accionBd, String estadoResultado) {
        this.accionBd = accionBd;
        this.estadoResultado = estadoResultado;
    }

    public String accionBd() {
        return accionBd;
    }

    public String estadoResultado() {
        return estadoResultado;
    }

    public static ReservaGestionAccion fromValue(String value) {
        if (value == null) {
            throw new IllegalArgumentException("La acción de gestión no puede ser nula");
        }
        return switch (value.trim().toLowerCase()) {
            case "aprobar" -> APROBAR;
            case "rechazar" -> RECHAZAR;
            default -> throw new IllegalArgumentException("Acción de gestión desconocida: " + value);
        };
    }
}