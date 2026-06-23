package com.horariocurso.dto;

import com.horariocurso.modelo.DiaSemana;
import jakarta.validation.constraints.Min;
import jakarta.validation.constraints.NotNull;
import java.time.LocalDate;

public class HorarioCursoRequest {

    @NotNull
    @Min(1)
    private Integer curso;

    @NotNull
    @Min(1)
    private Integer docente;

    @NotNull
    @Min(1)
    private Integer espacio;

    @NotNull
    @Min(1)
    private Integer bloque;

    @NotNull
    private DiaSemana diaSemana;

    @NotNull
    private LocalDate fechaInicio;

    @NotNull
    private LocalDate fechaFin;

    @NotNull
    private Boolean estado;

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

    public DiaSemana getDiaSemana() {
        return diaSemana;
    }

    public void setDiaSemana(DiaSemana diaSemana) {
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
}
