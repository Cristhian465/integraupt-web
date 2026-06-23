package com.Sanciones.dto;

import com.Sanciones.model.SancionEstado;

import java.time.LocalDate;

public class VerificacionSancionResponse {

    private boolean sancionado;
    private Long sancionId;
    private String motivo;
    private LocalDate fechaFin;
    private SancionEstado estado;

    public boolean isSancionado() {
        return sancionado;
    }

    public void setSancionado(boolean sancionado) {
        this.sancionado = sancionado;
    }

    public Long getSancionId() {
        return sancionId;
    }

    public void setSancionId(Long sancionId) {
        this.sancionId = sancionId;
    }

    public String getMotivo() {
        return motivo;
    }

    public void setMotivo(String motivo) {
        this.motivo = motivo;
    }

    public LocalDate getFechaFin() {
        return fechaFin;
    }

    public void setFechaFin(LocalDate fechaFin) {
        this.fechaFin = fechaFin;
    }

    public SancionEstado getEstado() {
        return estado;
    }

    public void setEstado(SancionEstado estado) {
        this.estado = estado;
    }
}