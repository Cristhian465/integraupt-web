package com.Sanciones.model;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.EnumType;
import jakarta.persistence.Enumerated;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.Table;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Size;

import java.time.LocalDate;

@Entity
@Table(name = "sancion")
public class Sancion {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "IdSancion")
    private Long id;

    @NotNull
    @Column(name = "Usuario", nullable = false)
    private Long usuarioId;

    @NotNull
    @Enumerated(EnumType.STRING)
    @Column(name = "TipoUsuario", length = 20, nullable = false)
    private TipoUsuario tipoUsuario;

    @NotBlank
    @Size(max = 65535)
    @Column(name = "Motivo", nullable = false, columnDefinition = "TEXT")
    private String motivo;

    @NotNull
    @Column(name = "FechaInicio", nullable = false)
    private LocalDate fechaInicio;

    @NotNull
    @Column(name = "FechaFin", nullable = false)
    private LocalDate fechaFin;

    @NotNull
    @Enumerated(EnumType.STRING)
    @Column(name = "Estado", nullable = false, length = 20)
    private SancionEstado estado;

    public Sancion() {
        // Constructor requerido por JPA
    }

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