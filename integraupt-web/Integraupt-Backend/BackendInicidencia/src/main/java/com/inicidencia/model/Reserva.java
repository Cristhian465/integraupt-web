package com.inicidencia.model;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.FetchType;
import jakarta.persistence.Id;
import jakarta.persistence.JoinColumn;
import jakarta.persistence.ManyToOne;
import jakarta.persistence.Table;

import java.time.LocalDate;

@Entity
@Table(name = "reserva")
public class Reserva {

    @Id
    @Column(name = "IdReserva")
    private Integer id;

    @Column(name = "usuario", nullable = false)
    private Integer usuarioId;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "usuario", insertable = false, updatable = false)
    private Usuario usuario;

    @Column(name = "espacio", nullable = false)
    private Integer espacioId;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "espacio", insertable = false, updatable = false)
    private Espacio espacio;

    @Column(name = "bloque", nullable = false)
    private Integer bloqueId;

    @Column(name = "fechaReserva", nullable = false)
    private LocalDate fechaReserva;

    @Column(name = "Estado")
    private String estado;

    public Reserva() {
        // Constructor requerido por JPA
    }

    public Integer getId() {
        return id;
    }

    public void setId(Integer id) {
        this.id = id;
    }

    public Integer getUsuarioId() {
        return usuarioId;
    }

    public void setUsuarioId(Integer usuarioId) {
        this.usuarioId = usuarioId;
    }

    public Usuario getUsuario() {
        return usuario;
    }

    public void setUsuario(Usuario usuario) {
        this.usuario = usuario;
    }

    public Integer getEspacioId() {
        return espacioId;
    }

    public void setEspacioId(Integer espacioId) {
        this.espacioId = espacioId;
    }

    public Espacio getEspacio() {
        return espacio;
    }

    public void setEspacio(Espacio espacio) {
        this.espacio = espacio;
    }

    public Integer getBloqueId() {
        return bloqueId;
    }

    public void setBloqueId(Integer bloqueId) {
        this.bloqueId = bloqueId;
    }

    public LocalDate getFechaReserva() {
        return fechaReserva;
    }

    public void setFechaReserva(LocalDate fechaReserva) {
        this.fechaReserva = fechaReserva;
    }

    public String getEstado() {
        return estado;
    }

    public void setEstado(String estado) {
        this.estado = estado;
    }
}