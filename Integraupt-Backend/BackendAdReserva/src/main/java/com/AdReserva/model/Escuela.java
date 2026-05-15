package com.AdReserva.model;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.Id;
import jakarta.persistence.Table;

@Entity
@Table(name = "escuela")
public class Escuela {

    @Id
    @Column(name = "IdEscuela")
    private Integer id;

    @Column(name = "IdFacultad", nullable = false)
    private Integer facultadId;

    @Column(name = "Nombre", nullable = false)
    private String nombre;

    public Integer getId() {
        return id;
    }

    public void setId(Integer id) {
        this.id = id;
    }

    public Integer getFacultadId() {
        return facultadId;
    }

    public void setFacultadId(Integer facultadId) {
        this.facultadId = facultadId;
    }

    public String getNombre() {
        return nombre;
    }

    public void setNombre(String nombre) {
        this.nombre = nombre;
    }
}