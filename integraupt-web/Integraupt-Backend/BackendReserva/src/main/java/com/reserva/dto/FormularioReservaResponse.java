package com.reserva.dto;

import com.reserva.model.Espacio;

import java.util.List;

public class FormularioReservaResponse {

    private final List<Espacio> espacios;
    private final Integer espacioIdSeleccionado;
    private final String espacioSeleccionDescripcion;

    public FormularioReservaResponse(List<Espacio> espacios, Integer espacioIdSeleccionado, String espacioSeleccionDescripcion) {
        this.espacios = espacios;
        this.espacioIdSeleccionado = espacioIdSeleccionado;
        this.espacioSeleccionDescripcion = espacioSeleccionDescripcion;
    }

    public List<Espacio> getEspacios() {
        return espacios;
    }

    public Integer getEspacioIdSeleccionado() {
        return espacioIdSeleccionado;
    }

    public String getEspacioSeleccionDescripcion() {
        return espacioSeleccionDescripcion;
    }
}