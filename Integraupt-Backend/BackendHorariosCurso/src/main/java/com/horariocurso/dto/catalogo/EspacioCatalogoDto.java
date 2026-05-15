package com.horariocurso.dto.catalogo;

public class EspacioCatalogoDto {

    private Integer id;
    private String codigo;
    private String nombre;
    private String tipo;
    private Integer capacidad;

    public EspacioCatalogoDto() {
    }

    public EspacioCatalogoDto(Integer id, String codigo, String nombre, String tipo, Integer capacidad) {
        this.id = id;
        this.codigo = codigo;
        this.nombre = nombre;
        this.tipo = tipo;
        this.capacidad = capacidad;
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
}
