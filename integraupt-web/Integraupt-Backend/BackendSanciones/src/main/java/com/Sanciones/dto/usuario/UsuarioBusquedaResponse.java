package com.Sanciones.dto.usuario;

public class UsuarioBusquedaResponse {

    private Long id;
    private String nombreCompleto;
    private String codigo;
    private Long escuelaId;
    private String escuelaNombre;
    private Long facultadId;
    private String facultadNombre;

    public UsuarioBusquedaResponse() {
    }

    public UsuarioBusquedaResponse(Long id,
                                   String nombreCompleto,
                                   String codigo,
                                   Long escuelaId,
                                   String escuelaNombre,
                                   Long facultadId,
                                   String facultadNombre) {
        this.id = id;
        this.nombreCompleto = nombreCompleto;
        this.codigo = codigo;
        this.escuelaId = escuelaId;
        this.escuelaNombre = escuelaNombre;
        this.facultadId = facultadId;
        this.facultadNombre = facultadNombre;
    }

    public Long getId() {
        return id;
    }

    public void setId(Long id) {
        this.id = id;
    }

    public String getNombreCompleto() {
        return nombreCompleto;
    }

    public void setNombreCompleto(String nombreCompleto) {
        this.nombreCompleto = nombreCompleto;
    }

    public String getCodigo() {
        return codigo;
    }

    public void setCodigo(String codigo) {
        this.codigo = codigo;
    }

    public Long getEscuelaId() {
        return escuelaId;
    }

    public void setEscuelaId(Long escuelaId) {
        this.escuelaId = escuelaId;
    }

    public String getEscuelaNombre() {
        return escuelaNombre;
    }

    public void setEscuelaNombre(String escuelaNombre) {
        this.escuelaNombre = escuelaNombre;
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