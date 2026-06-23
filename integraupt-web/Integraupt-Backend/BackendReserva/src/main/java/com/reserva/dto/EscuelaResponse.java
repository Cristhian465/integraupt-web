package com.reserva.dto;

import com.reserva.model.Escuela;
import com.reserva.model.Facultad;

public record EscuelaResponse(Integer id, String nombre, Integer facultadId, String facultadNombre) {

    public static EscuelaResponse fromEntity(Escuela escuela) {
        if (escuela == null) {
            return new EscuelaResponse(null, null, null, null);
        }

        Facultad facultad = escuela.getFacultad();
        String nombreFacultad = null;
        if (facultad != null) {
            nombreFacultad = facultad.getNombre();
        }

        return new EscuelaResponse(escuela.getId(), escuela.getNombre(), escuela.getFacultadId(), nombreFacultad);
    }
}