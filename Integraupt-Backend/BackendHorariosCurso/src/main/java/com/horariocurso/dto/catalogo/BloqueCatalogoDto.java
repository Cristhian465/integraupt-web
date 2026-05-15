package com.horariocurso.dto.catalogo;

public class BloqueCatalogoDto {

    private Integer id;
    private String nombre;
    private String horaInicio;
    private String horaFinal;

    public BloqueCatalogoDto() {
    }

    public BloqueCatalogoDto(Integer id, String nombre, String horaInicio, String horaFinal) {
        this.id = id;
        this.nombre = nombre;
        this.horaInicio = horaInicio;
        this.horaFinal = horaFinal;
    }

    public Integer getId() {
        return id;
    }

    public void setId(Integer id) {
        this.id = id;
    }

    public String getNombre() {
        return nombre;
    }

    public void setNombre(String nombre) {
        this.nombre = nombre;
    }

    public String getHoraInicio() {
        return horaInicio;
    }

    public void setHoraInicio(String horaInicio) {
        this.horaInicio = horaInicio;
    }

    public String getHoraFinal() {
        return horaFinal;
    }

    public void setHoraFinal(String horaFinal) {
        this.horaFinal = horaFinal;
    }
}
