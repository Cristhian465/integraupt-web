package com.horarios.model;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;
import jakarta.persistence.Table;

import java.time.LocalTime;

@Entity
@Table(name = "bloqueshorarios")
public class BloqueHorario {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "IdBloque")
    private Integer id;

    @Column(name = "Orden", nullable = false)
    private Integer orden;

    @Column(name = "Nombre", nullable = false, length = 50)
    private String nombre;

    @Column(name = "HoraInicio", nullable = false)
    private LocalTime horaInicio;

    @Column(name = "HoraFinal", nullable = false)
    private LocalTime horaFin;

    public Integer getId() {
        return id;
    }

    public Integer getOrden() {
        return orden;
    }

    public void setOrden(Integer orden) {
        this.orden = orden;
    }

    public String getNombre() {
        return nombre;
    }

    public void setNombre(String nombre) {
        this.nombre = nombre;
    }

    public LocalTime getHoraInicio() {
        return horaInicio;
    }

    public void setHoraInicio(LocalTime horaInicio) {
        this.horaInicio = horaInicio;
    }

    public LocalTime getHoraFin() {
        return horaFin;
    }

    public void setHoraFin(LocalTime horaFin) {
        this.horaFin = horaFin;
    }
}