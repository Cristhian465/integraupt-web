package com.Sanciones.dto.catalogo;

public class FacultadCatalogResponse {

    private Long id;
    private String nombre;
    private String abreviatura;

    public FacultadCatalogResponse() {
    }

    public FacultadCatalogResponse(Long id, String nombre, String abreviatura) {
        this.id = id;
        this.nombre = nombre;
        this.abreviatura = abreviatura;
    }

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

    public String getAbreviatura() {
        return abreviatura;
    }

    public void setAbreviatura(String abreviatura) {
        this.abreviatura = abreviatura;
    }
}