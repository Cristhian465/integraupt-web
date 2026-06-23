package com.horariocurso.dto;

import java.time.LocalDate;

public class HorarioCursoResponse {

    private Integer idHorarioCurso;
    private Integer curso;
    private Integer docente;
    private Integer espacio;
    private Integer bloque;
    private String diaSemana;
    private LocalDate fechaInicio;
    private LocalDate fechaFin;
    private Boolean estado;
    private String nombreCurso;
    private String nombreDocente;
    private String nombreEspacio;
    private String codigoEspacio;
    private String nombreBloque;
    private String horaInicioBloque;
    private String horaFinBloque;

    public HorarioCursoResponse() {
    }

    public Integer getIdHorarioCurso() {
        return idHorarioCurso;
    }

    public void setIdHorarioCurso(Integer idHorarioCurso) {
        this.idHorarioCurso = idHorarioCurso;
    }

    public Integer getCurso() {
        return curso;
    }

    public void setCurso(Integer curso) {
        this.curso = curso;
    }

    public Integer getDocente() {
        return docente;
    }

    public void setDocente(Integer docente) {
        this.docente = docente;
    }

    public Integer getEspacio() {
        return espacio;
    }

    public void setEspacio(Integer espacio) {
        this.espacio = espacio;
    }

    public Integer getBloque() {
        return bloque;
    }

    public void setBloque(Integer bloque) {
        this.bloque = bloque;
    }

    public String getDiaSemana() {
        return diaSemana;
    }

    public void setDiaSemana(String diaSemana) {
        this.diaSemana = diaSemana;
    }

    public LocalDate getFechaInicio() {
        return fechaInicio;
    }

    public void setFechaInicio(LocalDate fechaInicio) {
        this.fechaInicio = fechaInicio;
    }

    public LocalDate getFechaFin() {
        return fechaFin;
    }

    public void setFechaFin(LocalDate fechaFin) {
        this.fechaFin = fechaFin;
    }

    public Boolean getEstado() {
        return estado;
    }

    public void setEstado(Boolean estado) {
        this.estado = estado;
    }

    public String getNombreCurso() {
        return nombreCurso;
    }

    public void setNombreCurso(String nombreCurso) {
        this.nombreCurso = nombreCurso;
    }

    public String getNombreDocente() {
        return nombreDocente;
    }

    public void setNombreDocente(String nombreDocente) {
        this.nombreDocente = nombreDocente;
    }

    public String getNombreEspacio() {
        return nombreEspacio;
    }

    public void setNombreEspacio(String nombreEspacio) {
        this.nombreEspacio = nombreEspacio;
    }

    public String getCodigoEspacio() {
        return codigoEspacio;
    }

    public void setCodigoEspacio(String codigoEspacio) {
        this.codigoEspacio = codigoEspacio;
    }

    public String getNombreBloque() {
        return nombreBloque;
    }

    public void setNombreBloque(String nombreBloque) {
        this.nombreBloque = nombreBloque;
    }

    public String getHoraInicioBloque() {
        return horaInicioBloque;
    }

    public void setHoraInicioBloque(String horaInicioBloque) {
        this.horaInicioBloque = horaInicioBloque;
    }

    public String getHoraFinBloque() {
        return horaFinBloque;
    }

    public void setHoraFinBloque(String horaFinBloque) {
        this.horaFinBloque = horaFinBloque;
    }
}
