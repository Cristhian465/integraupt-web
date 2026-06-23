package com.reserva.dto;

public class ReservaQrInfo {

    private Long reservaId;
    private String laboratorio;
    private String fecha;
    private String hora;
    private String estado;
    private String solicitanteNombre;
    private String solicitanteCodigo;

    public Long getReservaId() {
        return reservaId;
    }

    public void setReservaId(Long reservaId) {
        this.reservaId = reservaId;
    }

    public String getLaboratorio() {
        return laboratorio;
    }

    public void setLaboratorio(String laboratorio) {
        this.laboratorio = laboratorio;
    }

    public String getFecha() {
        return fecha;
    }

    public void setFecha(String fecha) {
        this.fecha = fecha;
    }

    public String getHora() {
        return hora;
    }

    public void setHora(String hora) {
        this.hora = hora;
    }

    public String getEstado() {
        return estado;
    }

    public void setEstado(String estado) {
        this.estado = estado;
    }

    public String getSolicitanteNombre() {
        return solicitanteNombre;
    }

    public void setSolicitanteNombre(String solicitanteNombre) {
        this.solicitanteNombre = solicitanteNombre;
    }

    public String getSolicitanteCodigo() {
        return solicitanteCodigo;
    }

    public void setSolicitanteCodigo(String solicitanteCodigo) {
        this.solicitanteCodigo = solicitanteCodigo;
    }
}