package com.integraupt.qr.dto;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Size;

public class ReservaQrRequest {

    @NotNull(message = "El identificador de la reserva es obligatorio")
    private Long reservaId;

    @NotBlank(message = "El laboratorio es obligatorio")
    private String laboratorio;

    @NotBlank(message = "La fecha es obligatoria")
    private String fecha;

    @NotBlank(message = "La hora es obligatoria")
    private String hora;

    @NotBlank(message = "El estado es obligatorio")
    private String estado;

    @NotBlank(message = "El nombre del solicitante es obligatorio")
    private String solicitanteNombre;

    @NotBlank(message = "El código del solicitante es obligatorio")
    private String solicitanteCodigo;

    @Size(max = 255, message = "El token no debe exceder los 255 caracteres")
    private String token;

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

    public String getToken() {
        return token;
    }

    public void setToken(String token) {
        this.token = token;
    }
}