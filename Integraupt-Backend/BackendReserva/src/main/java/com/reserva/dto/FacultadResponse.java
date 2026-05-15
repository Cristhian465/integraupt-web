package com.reserva.dto;

import com.reserva.model.Facultad;

public record FacultadResponse(Integer id, String nombre, String abreviatura) {

    public static FacultadResponse fromEntity(Facultad facultad) {
        if (facultad == null) {
            return new FacultadResponse(null, null, null);
        }
        return new FacultadResponse(facultad.getId(), facultad.getNombre(), facultad.getAbreviatura());
    }
}