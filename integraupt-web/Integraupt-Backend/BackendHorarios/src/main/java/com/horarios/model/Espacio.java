package com.horarios.model;

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

    @Column(name = "Codigo", nullable = false, length = 20)
    private String codigo;

    @Column(name = "Nombre", nullable = false, length = 100)
    private String nombre;

    @Column(name = "Tipo", nullable = false, length = 20)
    private String tipo;

    @Column(name = "Capacidad", nullable = false)
    private Integer capacidad;

    public Integer getId() {
        return id;
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
}