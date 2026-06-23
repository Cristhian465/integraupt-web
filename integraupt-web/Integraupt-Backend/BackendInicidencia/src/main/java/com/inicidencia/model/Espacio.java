package com.inicidencia.model;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.FetchType;
import jakarta.persistence.Id;
import jakarta.persistence.JoinColumn;
import jakarta.persistence.ManyToOne;
import jakarta.persistence.Table;

@Entity
@Table(name = "espacio")
public class Espacio {

    @Id
    @Column(name = "IdEspacio")
    private Integer id;

    @Column(name = "Codigo")
    private String codigo;

    @Column(name = "Nombre", nullable = false)
    private String nombre;

    @Column(name = "Tipo")
    private String tipo;

    @Column(name = "Capacidad")
    private Integer capacidad;

    @Column(name = "Equipamiento")
    private String equipamiento;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "Escuela")
    private Escuela escuela;

    @Column(name = "Escuela", insertable = false, updatable = false)
    private Long escuelaId;

    @Column(name = "Estado")
    private Integer estado;

    public Espacio() {
        // Constructor requerido por JPA
    }

    public Integer getId() {
        return id;
    }

    public void setId(Integer id) {
        this.id = id;
    }

    public String getCodigo() {
        return codigo;
    }

    public void setCodigo(String codigo) {
        this.codigo = codigo;
    }

    public String getNombre() {
        return nombre;
    }

    public void setNombre(String nombre) {
        this.nombre = nombre;
    }

    public String getTipo() {
        return tipo;
    }

    public void setTipo(String tipo) {
        this.tipo = tipo;
    }

    public Integer getCapacidad() {
        return capacidad;
    }

    public void setCapacidad(Integer capacidad) {
        this.capacidad = capacidad;
    }

    public String getEquipamiento() {
        return equipamiento;
    }

    public void setEquipamiento(String equipamiento) {
        this.equipamiento = equipamiento;
    }

    public Escuela getEscuela() {
        return escuela;
    }

    public void setEscuela(Escuela escuela) {
        this.escuela = escuela;
    }

    public Long getEscuelaId() {
        return escuelaId;
    }

    public void setEscuelaId(Long escuelaId) {
        this.escuelaId = escuelaId;
    }

    public Integer getEstado() {
        return estado;
    }

    public void setEstado(Integer estado) {
        this.estado = estado;
    }
}