package com.AdReserva.model;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.Table;

@Entity
@Table(name = "administrativo")
public class Administrativo {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "IdAdministrativo")
    private Integer id;

    @Column(name = "IdUsuario", nullable = false)
    private Integer usuarioId;

    @Column(name = "Escuela")
    private Integer escuelaId;

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

    public Integer getEscuelaId() {
        return escuelaId;
    }

    public void setEscuelaId(Integer escuelaId) {
        this.escuelaId = escuelaId;
    }
}