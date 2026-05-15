package com.AdReserva.model;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.Id;
import jakarta.persistence.Table;

@Entity
@Table(name = "espacio")
public class Espacio {

    @Id
    @Column(name = "IdEspacio")
    private Integer id;

    @Column(name = "Codigo", nullable = false)
    private String codigo;

    @Column(name = "Nombre", nullable = false)
    private String nombre;

    @Column(name = "Tipo", nullable = false)
    private String tipo;

    @Column(name = "Escuela", nullable = false)
    private Integer escuelaId;

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

    public Integer getEscuelaId() {
        return escuelaId;
    }

    public void setEscuelaId(Integer escuelaId) {
        this.escuelaId = escuelaId;
    }
}