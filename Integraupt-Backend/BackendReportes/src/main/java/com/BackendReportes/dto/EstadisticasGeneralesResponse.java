package com.BackendReportes.dto;

public class EstadisticasGeneralesResponse {
    private Integer totalEstudiantes;
    private Integer totalDocentes;
    private Integer reservasActivas;
    private Double tasaUso;
    private Integer reservasEsteMes;
    private Integer reservasMesAnterior;
    private String variacionReservas;

    public EstadisticasGeneralesResponse() {
    }

    public EstadisticasGeneralesResponse(Integer totalEstudiantes, Integer totalDocentes, 
                                       Integer reservasActivas, Double tasaUso, 
                                       Integer reservasEsteMes, Integer reservasMesAnterior, 
                                       String variacionReservas) {
        this.totalEstudiantes = totalEstudiantes;
        this.totalDocentes = totalDocentes;
        this.reservasActivas = reservasActivas;
        this.tasaUso = tasaUso;
        this.reservasEsteMes = reservasEsteMes;
        this.reservasMesAnterior = reservasMesAnterior;
        this.variacionReservas = variacionReservas;
    }

    // Getters y Setters
    public Integer getTotalEstudiantes() {
        return totalEstudiantes;
    }

    public void setTotalEstudiantes(Integer totalEstudiantes) {
        this.totalEstudiantes = totalEstudiantes;
    }

    public Integer getTotalDocentes() {
        return totalDocentes;
    }

    public void setTotalDocentes(Integer totalDocentes) {
        this.totalDocentes = totalDocentes;
    }

    public Integer getReservasActivas() {
        return reservasActivas;
    }

    public void setReservasActivas(Integer reservasActivas) {
        this.reservasActivas = reservasActivas;
    }

    public Double getTasaUso() {
        return tasaUso;
    }

    public void setTasaUso(Double tasaUso) {
        this.tasaUso = tasaUso;
    }

    public Integer getReservasEsteMes() {
        return reservasEsteMes;
    }

    public void setReservasEsteMes(Integer reservasEsteMes) {
        this.reservasEsteMes = reservasEsteMes;
    }

    public Integer getReservasMesAnterior() {
        return reservasMesAnterior;
    }

    public void setReservasMesAnterior(Integer reservasMesAnterior) {
        this.reservasMesAnterior = reservasMesAnterior;
    }

    public String getVariacionReservas() {
        return variacionReservas;
    }

    public void setVariacionReservas(String variacionReservas) {
        this.variacionReservas = variacionReservas;
    }
}