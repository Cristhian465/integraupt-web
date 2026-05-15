package com.BackendReportes.dto;

public class ReservasMesResponse {
    private String mes;
    private Integer anio;
    private Integer totalReservas;

    public ReservasMesResponse() {
    }

    public ReservasMesResponse(String mes, Integer anio, Integer totalReservas) {
        this.mes = mes;
        this.anio = anio;
        this.totalReservas = totalReservas;
    }

    // Getters y Setters
    public String getMes() {
        return mes;
    }

    public void setMes(String mes) {
        this.mes = mes;
    }

    public Integer getAnio() {
        return anio;
    }

    public void setAnio(Integer anio) {
        this.anio = anio;
    }

    public Integer getTotalReservas() {
        return totalReservas;
    }

    public void setTotalReservas(Integer totalReservas) {
        this.totalReservas = totalReservas;
    }
}