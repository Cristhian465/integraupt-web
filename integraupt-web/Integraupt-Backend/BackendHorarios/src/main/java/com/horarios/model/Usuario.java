package com.horarios.model;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.Table;

@Entity
@Table(name = "usuario")
public class Usuario {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "IdUsuario")
    private Integer id;

    @Column(name = "Nombre", nullable = false, length = 30)
    private String nombres;

    @Column(name = "Apellido", nullable = false, length = 30)
    private String apellidos;

    public Integer getId() {
        return id;
    }

    public String getNombres() {
        return nombres;
    }

    public void setNombres(String nombres) {
        this.nombres = nombres;
    }

    public String getApellidos() {
        return apellidos;
    }

    public void setApellidos(String apellidos) {
        this.apellidos = apellidos;
    }

    public String getNombreCompleto() {
        return String.format("%s %s", nombres != null ? nombres : "", apellidos != null ? apellidos : "").trim();
    }
}