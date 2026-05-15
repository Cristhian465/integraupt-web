package com.AdReserva.model;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.PrePersist;
import jakarta.persistence.Table;

import java.time.LocalDateTime;

@Entity
@Table(name = "reserva_gestion")
public class ReservaGestion {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "IdGestion")
    private Integer id;

    @Column(name = "IdReserva", nullable = false)
    private Integer reservaId;

    @Column(name = "UsuarioGestion", nullable = false)
    private Integer usuarioGestionId;

    @Column(name = "FechaGestion", nullable = false)
    private LocalDateTime fechaGestion;

    @Column(name = "Accion", nullable = false)
    private String accion;

    @Column(name = "Motivo", nullable = false)
    private String motivo;

    @Column(name = "Comentarios")
    private String comentarios;

    public Integer getId() {
        return id;
    }

    public void setId(Integer id) {
        this.id = id;
    }

    public Integer getReservaId() {
        return reservaId;
    }

    public void setReservaId(Integer reservaId) {
        this.reservaId = reservaId;
    }

    public Integer getUsuarioGestionId() {
        return usuarioGestionId;
    }

    public void setUsuarioGestionId(Integer usuarioGestionId) {
        this.usuarioGestionId = usuarioGestionId;
    }

    public LocalDateTime getFechaGestion() {
        return fechaGestion;
    }

    public void setFechaGestion(LocalDateTime fechaGestion) {
        this.fechaGestion = fechaGestion;
    }

    public String getAccion() {
        return accion;
    }

    public void setAccion(String accion) {
        this.accion = accion;
    }

    public String getMotivo() {
        return motivo;
    }

    public void setMotivo(String motivo) {
        this.motivo = motivo;
    }

    public String getComentarios() {
        return comentarios;
    }

    public void setComentarios(String comentarios) {
        this.comentarios = comentarios;
    }

    @PrePersist
    public void prePersist() {
        if (fechaGestion == null) {
            fechaGestion = LocalDateTime.now();
        }
    }
}