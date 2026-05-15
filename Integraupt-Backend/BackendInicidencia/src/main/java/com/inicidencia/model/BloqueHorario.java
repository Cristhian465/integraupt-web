package com.inicidencia.model;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.Id;
import jakarta.persistence.Table;

import java.time.LocalTime;

@Entity
@Table(name = "bloqueshorarios")
public class BloqueHorario {

    @Id
    @Column(name = "IdBloque")
    private Integer id;

    @Column(name = "HoraInicio", nullable = false)
    private LocalTime horaInicio;

    @Column(name = "HoraFinal", nullable = false)
    private LocalTime horaFinal;

    public BloqueHorario() {
        // Constructor requerido por JPA
    }

    public Integer getId() {
        return id;
    }

    public void setId(Integer id) {
        this.id = id;
    }

    public LocalTime getHoraInicio() {
        return horaInicio;
    }

    public void setHoraInicio(LocalTime horaInicio) {
        this.horaInicio = horaInicio;
    }

    public LocalTime getHoraFinal() {
        return horaFinal;
    }

    public void setHoraFinal(LocalTime horaFinal) {
        this.horaFinal = horaFinal;
    }
}