package com.integraupt.qr.dto;

import java.io.Serial;
import java.io.Serializable;

public class ReservaInfo implements Serializable {

    @Serial
    private static final long serialVersionUID = 1L;

    private final Long reservaId;
    private final String laboratorio;
    private final String fecha;
    private final String hora;
    private final String estado;

    private final String solicitanteNombre;
    private final String solicitanteCodigo;

    public ReservaInfo(Long reservaId,
                       String laboratorio,
                       String fecha,
                       String hora,
                       String estado,
                       String solicitanteNombre,
                       String solicitanteCodigo) {
        this.reservaId = reservaId;
        this.laboratorio = laboratorio;
        this.fecha = fecha;
        this.hora = hora;
        this.estado = estado;
        this.solicitanteNombre = solicitanteNombre;
        this.solicitanteCodigo = solicitanteCodigo;
    }

    public Long getReservaId() {
        return reservaId;
    }

    public String getLaboratorio() {
        return laboratorio;
    }

    public String getFecha() {
        return fecha;
    }

    public String getHora() {
        return hora;
    }

    public String getEstado() {
        return estado;
    }

    public String getSolicitanteNombre() {
        return solicitanteNombre;
    }

    public String getSolicitanteCodigo() {
        return solicitanteCodigo;
    }
}