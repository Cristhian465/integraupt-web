package com.Sanciones.dto.catalogo;

public class EscuelaCatalogResponse {

    private Long id;
    private String nombre;
    private Long facultadId;
    private String facultadNombre;

    public EscuelaCatalogResponse() {
    }

    public EscuelaCatalogResponse(Long id, String nombre, Long facultadId, String facultadNombre) {
        this.id = id;
        this.nombre = nombre;
        this.facultadId = facultadId;
        this.facultadNombre = facultadNombre;
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

    public Long getFacultadId() {
        return facultadId;
    }

    public void setFacultadId(Long facultadId) {
        this.facultadId = facultadId;
    }

    public String getFacultadNombre() {
        return facultadNombre;
    }

    public void setFacultadNombre(String facultadNombre) {
        this.facultadNombre = facultadNombre;
    }
}