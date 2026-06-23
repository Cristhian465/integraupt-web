package com.Sanciones.dto;

import jakarta.validation.constraints.FutureOrPresent;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Size;

import java.time.LocalDate;

public class SancionRequest {


    private Long usuarioId;

    @Size(max = 50)
    private String usuarioCodigo;

    @Size(max = 255)
    private String usuarioNombre;


    @NotBlank
    private String tipoUsuario;

    @Size(max = 30)
    private String rol;

    private Long facultadId;

    private Long escuelaId;

    private Long escuelaContextoId;

    @NotBlank
    @Size(max = 255)
    private String motivo;

    @NotNull
    private LocalDate fechaInicio;

    @NotNull
    @FutureOrPresent
    private LocalDate fechaFin;

    public Long getUsuarioId() {
        return usuarioId;
    }

    public void setUsuarioId(Long usuarioId) {
        this.usuarioId = usuarioId;
    }

    public String getUsuarioCodigo() {
        return usuarioCodigo;
    }

    public void setUsuarioCodigo(String usuarioCodigo) {
        this.usuarioCodigo = usuarioCodigo;
    }

    public String getUsuarioNombre() {
        return usuarioNombre;
    }

    public void setUsuarioNombre(String usuarioNombre) {
        this.usuarioNombre = usuarioNombre;
    }

    public String getTipoUsuario() {
        return tipoUsuario;
    }

    public void setTipoUsuario(String tipoUsuario) {
        this.tipoUsuario = tipoUsuario;
    }

    public String getRol() {
        return rol;
    }

    public void setRol(String rol) {
        this.rol = rol;
    }

    public Long getFacultadId() {
        return facultadId;
    }

    public void setFacultadId(Long facultadId) {
        this.facultadId = facultadId;
    }

    public Long getEscuelaId() {
        return escuelaId;
    }

    public void setEscuelaId(Long escuelaId) {
        this.escuelaId = escuelaId;
    }

    public Long getEscuelaContextoId() {
        return escuelaContextoId;
    }

    public void setEscuelaContextoId(Long escuelaContextoId) {
        this.escuelaContextoId = escuelaContextoId;
    }

    public String getMotivo() {
        return motivo;
    }

    public void setMotivo(String motivo) {
        this.motivo = motivo;
    }

    public LocalDate getFechaInicio() {
        return fechaInicio;
    }

    public void setFechaInicio(LocalDate fechaInicio) {
        this.fechaInicio = fechaInicio;
    }

    public LocalDate getFechaFin() {
        return fechaFin;
    }

    public void setFechaFin(LocalDate fechaFin) {
        this.fechaFin = fechaFin;
    }
}