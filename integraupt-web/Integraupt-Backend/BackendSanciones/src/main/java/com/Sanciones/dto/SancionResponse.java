package com.Sanciones.dto;

import com.Sanciones.model.SancionEstado;
import com.Sanciones.model.TipoUsuario;

import java.time.LocalDate;
public class SancionResponse {

    private Long id;
    private Long usuarioId;
    private String usuarioNombre;
    private String usuarioCodigo;
    private String usuarioEscuela;
    private Long usuarioEscuelaId;
    private String usuarioFacultad;
    private Long usuarioFacultadId;
    private TipoUsuario tipoUsuario;
    private String motivo;
    private LocalDate fechaInicio;
    private LocalDate fechaFin;
    private SancionEstado estado;

    public Long getId() {
        return id;
    }

    public void setId(Long id) {
        this.id = id;
    }

    public Long getUsuarioId() {
        return usuarioId;
    }

    public void setUsuarioId(Long usuarioId) {
        this.usuarioId = usuarioId;
    }

    public String getUsuarioNombre() {
        return usuarioNombre;
    }

    public void setUsuarioNombre(String usuarioNombre) {
        this.usuarioNombre = usuarioNombre;
    }

    public String getUsuarioCodigo() {
        return usuarioCodigo;
    }

    public void setUsuarioCodigo(String usuarioCodigo) {
        this.usuarioCodigo = usuarioCodigo;
    }

    public String getUsuarioEscuela() {
        return usuarioEscuela;
    }

    public void setUsuarioEscuela(String usuarioEscuela) {
        this.usuarioEscuela = usuarioEscuela;
    }

    public Long getUsuarioEscuelaId() {
        return usuarioEscuelaId;
    }

    public void setUsuarioEscuelaId(Long usuarioEscuelaId) {
        this.usuarioEscuelaId = usuarioEscuelaId;
    }

    public String getUsuarioFacultad() {
        return usuarioFacultad;
    }

    public void setUsuarioFacultad(String usuarioFacultad) {
        this.usuarioFacultad = usuarioFacultad;
    }

    public Long getUsuarioFacultadId() {
        return usuarioFacultadId;
    }

    public void setUsuarioFacultadId(Long usuarioFacultadId) {
        this.usuarioFacultadId = usuarioFacultadId;
    }

    public TipoUsuario getTipoUsuario() {
        return tipoUsuario;
    }

    public void setTipoUsuario(TipoUsuario tipoUsuario) {
        this.tipoUsuario = tipoUsuario;
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

    public SancionEstado getEstado() {
        return estado;
    }

    public void setEstado(SancionEstado estado) {
        this.estado = estado;
    }

}