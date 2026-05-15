package model;

public enum Turno {
    MAÑANA("Mañana"),
    TARDE("Tarde"),
    NOCHE("Noche"),
    COMPLETO("Completo");

    private final String descripcion;

    Turno(String descripcion) {
        this.descripcion = descripcion;
    }

    public String getDescripcion() {
        return descripcion;
    }

    public static Turno fromDescripcion(String descripcion) {
        for (Turno turno : Turno.values()) {
            if (turno.descripcion.equalsIgnoreCase(descripcion)) {
                return turno;
            }
        }
        throw new IllegalArgumentException("Turno no válido: " + descripcion);
    }
}