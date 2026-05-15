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

@Entity
@Table(name = "horarios")
public class Horario {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "IdHorario")
    private Integer id;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "espacio", nullable = false)
    private Espacio espacio;

    @ManyToOne(fetch = FetchType.LAZY)
    @JoinColumn(name = "bloque", nullable = false)
    private BloqueHorario bloque;

    @Convert(converter = DiaSemanaConverter.class)
    @Column(name = "diaSemana", nullable = false, length = 10)
    private DiaSemana diaSemana;

    @Column(name = "ocupado", nullable = false)
    private boolean ocupado;

    public Integer getId() {
        return id;
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

    public boolean isOcupado() {
        return ocupado;
    }

    public void setOcupado(boolean ocupado) {
        this.ocupado = ocupado;
    }
}