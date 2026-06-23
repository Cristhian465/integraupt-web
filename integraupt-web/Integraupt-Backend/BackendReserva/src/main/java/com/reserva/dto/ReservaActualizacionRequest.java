package com.reserva.dto;

import jakarta.validation.constraints.Min;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Size;

import java.time.LocalDate;

public class ReservaActualizacionRequest {

    @NotNull
    private Integer usuarioId;

    @NotNull
    private Integer espacioId;

    @NotNull
    private Integer bloqueId;

    @NotNull
    private Integer cursoId;

    @NotNull
    private LocalDate fechaReserva;

    @Size(max = 65535)
    private String descripcionUso;

    @NotNull
    @Min(1)
    private Integer cantidadEstudiantes;

    private String estado;

    public Integer getUsuarioId() {
        return usuarioId;
    }

    public void setUsuarioId(Integer usuarioId) {
        this.usuarioId = usuarioId;
    }

    public Integer getEspacioId() {
        return espacioId;
    }

    public void setEspacioId(Integer espacioId) {
        this.espacioId = espacioId;
    }

    public Integer getBloqueId() {
        return bloqueId;
    }

    public void setBloqueId(Integer bloqueId) {
        this.bloqueId = bloqueId;
    }

    public Integer getCursoId() {
        return cursoId;
    }

    public void setCursoId(Integer cursoId) {
        this.cursoId = cursoId;
    }

    public LocalDate getFechaReserva() {
        return fechaReserva;
    }

    public void setFechaReserva(LocalDate fechaReserva) {
        this.fechaReserva = fechaReserva;
    }

    public String getDescripcionUso() {
        return descripcionUso;
    }

    public void setDescripcionUso(String descripcionUso) {
        this.descripcionUso = descripcionUso;
    }

    public Integer getCantidadEstudiantes() {
        return cantidadEstudiantes;
    }

    public void setCantidadEstudiantes(Integer cantidadEstudiantes) {
        this.cantidadEstudiantes = cantidadEstudiantes;
    }

    public String getEstado() {
        return estado;
    }

    public void setEstado(String estado) {
        this.estado = estado;
    }
}