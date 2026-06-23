package com.BackendReportes.dto;

public class UsoEspacioResponse {
    private String nombreEspacio;
    private String codigoEspacio;
    private String tipoEspacio;
    private Integer totalReservas;
    private Double porcentajeUso;

    public UsoEspacioResponse() {
    }

    public UsoEspacioResponse(String nombreEspacio, String codigoEspacio, String tipoEspacio, 
                            Integer totalReservas, Double porcentajeUso) {
        this.nombreEspacio = nombreEspacio;
        this.codigoEspacio = codigoEspacio;
        this.tipoEspacio = tipoEspacio;
        this.totalReservas = totalReservas;
        this.porcentajeUso = porcentajeUso;
    }

    // Getters y Setters
    public String getNombreEspacio() {
        return nombreEspacio;
    }

    public void setNombreEspacio(String nombreEspacio) {
        this.nombreEspacio = nombreEspacio;
    }

    public String getCodigoEspacio() {
        return codigoEspacio;
    }

    public void setCodigoEspacio(String codigoEspacio) {
        this.codigoEspacio = codigoEspacio;
    }

    public String getTipoEspacio() {
        return tipoEspacio;
    }

    public void setTipoEspacio(String tipoEspacio) {
        this.tipoEspacio = tipoEspacio;
    }

    public Integer getTotalReservas() {
        return totalReservas;
    }

    public void setTotalReservas(Integer totalReservas) {
        this.totalReservas = totalReservas;
    }

    public Double getPorcentajeUso() {
        return porcentajeUso;
    }

    public void setPorcentajeUso(Double porcentajeUso) {
        this.porcentajeUso = porcentajeUso;
    }
}