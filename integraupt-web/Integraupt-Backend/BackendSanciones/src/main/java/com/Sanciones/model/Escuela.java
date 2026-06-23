package com.Sanciones.model;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.FetchType;
import jakarta.persistence.Id;
import jakarta.persistence.JoinColumn;
import jakarta.persistence.ManyToOne;
import jakarta.persistence.Table;

@Entity
@Table(name = "escuela")
public class Escuela {

    @Id
    @Column(name = "IdEscuela")
    private Long id;

    @Column(name = "Nombre", nullable = false)
    private String nombre;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "IdFacultad", nullable = false)
    private Facultad facultad;

    @Column(name = "IdFacultad", insertable = false, updatable = false)
    private Long facultadId;

    public Long getId() {
        return id;
    }

    public void setId(Long id) {
        this.id = id;
    }

    public String getNombre() {
        return nombre;
    }

    public void setNombre(String nombre) {
        this.nombre = nombre;
    }

    public Facultad getFacultad() {
        return facultad;
    }

    public void setFacultad(Facultad facultad) {
        this.facultad = facultad;
    }

    public Long getFacultadId() {
        return facultadId;
    }

    public void setFacultadId(Long facultadId) {
        this.facultadId = facultadId;
    }
}