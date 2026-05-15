package com.horariocurso.modelo;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.EnumType;
import jakarta.persistence.Enumerated;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.Table;
import java.time.LocalDate;

@Entity
@Table(name = "horario_curso")
public class HorarioCurso {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "IdHorarioCurso")
    private Integer id;

    @Column(name = "Curso", nullable = false)
    private Integer curso;

    @Column(name = "Docente", nullable = false)
    private Integer docente;

    @Column(name = "Espacio", nullable = false)
    private Integer espacio;

    @Column(name = "Bloque", nullable = false)
    private Integer bloque;

    @Enumerated(EnumType.STRING)
    @Column(name = "DiaSemana", nullable = false, length = 10)
    private DiaSemana diaSemana;

    @Column(name = "FechaInicio", nullable = false)
    private LocalDate fechaInicio;

    @Column(name = "FechaFin", nullable = false)
    private LocalDate fechaFin;

    @Column(name = "Estado", nullable = false)
    private Boolean estado = Boolean.TRUE;

    public HorarioCurso() {
    }

    public Integer getId() {
        return id;
    }

    public void setId(Integer id) {
        this.id = id;
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
