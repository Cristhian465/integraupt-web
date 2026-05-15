package com.reserva.model;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.Table;

@Entity
@Table(name = "espacio")
public class Espacio {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "IdEspacio")
    private Integer id;

    @Column(name = "Codigo", nullable = false)
    private String codigo;

    @Column(name = "Nombre", nullable = false)
    private String nombre;

    @Column(name = "Tipo", nullable = false)
    private String tipo;

    @Column(name = "Capacidad", nullable = false)
    private Integer capacidad;

    @Column(name = "Equipamiento")
    private String equipamiento;

    @Column(name = "Escuela", nullable = false)
    private Integer escuelaId;

    @Column(name = "Estado", nullable = false)
    private Integer estado;

    public Espacio() {
        // Constructor sin argumentos requerido por JPA
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

    public Integer getEscuelaId() {
        return escuelaId;
    }

    public void setEscuelaId(Integer escuelaId) {
        this.escuelaId = escuelaId;
    }

    public Integer getEstado() {
        return estado;
    }

    public void setEstado(Integer estado) {
        this.estado = estado;
    }

    public boolean estaActivo() {
        return estado != null && estado == 1;
    }
}