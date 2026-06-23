package com.horarios.model;

import com.horarios.model.converter.DiaSemanaConverter;
import jakarta.persistence.Convert;
import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.FetchType;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.JoinColumn;
import jakarta.persistence.ManyToOne;
import jakarta.persistence.Table;

import java.time.LocalDate;

@Entity
@Table(name = "horario_curso")
public class HorarioCurso {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "IdHorarioCurso")
    private Integer id;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "Curso", nullable = false)
    private Curso curso;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "Docente", nullable = false)
    private Usuario docente;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "Espacio", nullable = false)
    private Espacio espacio;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "Bloque", nullable = false)
    private BloqueHorario bloque;

    @Convert(converter = DiaSemanaConverter.class)
    @Column(name = "DiaSemana", nullable = false, length = 10)
    private DiaSemana diaSemana;

    @Column(name = "FechaInicio", nullable = false)
    private LocalDate fechaInicio;

    @Column(name = "FechaFin", nullable = false)
    private LocalDate fechaFin;

    @Column(name = "Estado", nullable = false)
    private boolean estado;

    public Integer getId() {
        return id;
    }

    public Curso getCurso() {
        return curso;
    }

    public void setCurso(Curso curso) {
        this.curso = curso;
    }

    public Usuario getDocente() {
        return docente;
    }

    public void setDocente(Usuario docente) {
        this.docente = docente;
    }

    public Espacio getEspacio() {
        return espacio;
    }

    public void setEspacio(Espacio espacio) {
        this.espacio = espacio;
    }

    public BloqueHorario getBloque() {
        return bloque;
    }

    public void setBloque(BloqueHorario bloque) {
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

    public boolean isEstado() {
        return estado;
    }

    public void setEstado(boolean estado) {
        this.estado = estado;
    }
}