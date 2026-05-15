package com.reserva.model;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.FetchType;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.JoinColumn;
import jakarta.persistence.ManyToOne;
import jakarta.persistence.Table;

@Entity
@Table(name = "escuela")
public class Escuela {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "IdEscuela")
    private Integer id;

    @Column(name = "Nombre", nullable = false)
    private String nombre;

    @Column(name = "IdFacultad", nullable = false)
    private Integer facultadId;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "IdFacultad", insertable = false, updatable = false)
    private Facultad facultad;

    public Escuela() {
        // Constructor requerido por JPA
    }

    public Integer getId() {
        return id;
    }

    public void setId(Integer id) {
        this.id = id;
    }

    public String getNombre() {
        return nombre;
    }

    public void setNombre(String nombre) {
        this.nombre = nombre;
    }

    public Integer getFacultadId() {
        return facultadId;
    }

    public void setFacultadId(Integer facultadId) {
        this.facultadId = facultadId;
    }

    public Facultad getFacultad() {
        return facultad;
    }

    public void setFacultad(Facultad facultad) {
        this.facultad = facultad;
    }
}