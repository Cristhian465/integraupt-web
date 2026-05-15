package model;

public enum TipoContrato {
    TIEMPO_COMPLETO("Tiempo Completo"),
    MEDIO_TIEMPO("Medio Tiempo"),
    CONTRATADO("Contratado"),
    NO_ESPECIFICADO(""); // Agregar este valor para manejar vacíos

    private final String descripcion;

    TipoContrato(String descripcion) {
        this.descripcion = descripcion;
    }

    public String getDescripcion() {
        return descripcion;
    }

    public static TipoContrato fromDescripcion(String descripcion) {
        if (descripcion == null || descripcion.trim().isEmpty()) {
            return NO_ESPECIFICADO; // Manejar valores vacíos
        }
        
        for (TipoContrato contrato : TipoContrato.values()) {
            if (contrato.descripcion.equalsIgnoreCase(descripcion)) {
                return contrato;
            }
        }
        throw new IllegalArgumentException("Tipo de contrato no válido: " + descripcion);
    }
}