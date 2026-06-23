package com.horariocurso.dto.catalogo;

public class DocenteCatalogoDto {

    private Integer id;
    private String nombres;
    private String apellidos;
    private String codigo;
    private String nombreCompleto;

    public DocenteCatalogoDto() {
    }

    public DocenteCatalogoDto(Integer id, String nombres, String apellidos, String codigo) {
        this.id = id;
        this.nombres = nombres;
        this.apellidos = apellidos;
        this.codigo = codigo;
        this.nombreCompleto = buildNombreCompleto();
    }

    private String buildNombreCompleto() {
        if (nombres == null && apellidos == null) {
            return null;
        }
        StringBuilder builder = new StringBuilder();
        if (nombres != null) {
            builder.append(nombres.trim());
        }
        if (apellidos != null) {
            if (builder.length() > 0) {
                builder.append(" ");
            }
            builder.append(apellidos.trim());
        }
        return builder.toString();
    }

    public Integer getId() {
        return id;
    }

    public void setId(Integer id) {
        this.id = id;
    }

    public String getNombres() {
        return nombres;
    }

    public void setNombres(String nombres) {
        this.nombres = nombres;
        this.nombreCompleto = buildNombreCompleto();
    }

    public String getApellidos() {
        return apellidos;
    }

    public void setApellidos(String apellidos) {
        this.apellidos = apellidos;
        this.nombreCompleto = buildNombreCompleto();
    }

    public String getCodigo() {
        return codigo;
    }

    public void setCodigo(String codigo) {
        this.codigo = codigo;
    }

    public String getNombreCompleto() {
        return nombreCompleto;
    }

    public void setNombreCompleto(String nombreCompleto) {
        this.nombreCompleto = nombreCompleto;
    }
}
