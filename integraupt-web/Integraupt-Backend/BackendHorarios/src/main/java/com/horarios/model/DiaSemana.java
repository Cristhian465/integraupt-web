package com.horarios.model;

import java.util.Arrays;

public enum DiaSemana {
    LUNES("Lunes"),
    MARTES("Martes"),
    MIERCOLES("Miercoles"),
    JUEVES("Jueves"),
    VIERNES("Viernes"),
    SABADO("Sabado");

    private final String nombre;

    DiaSemana(String nombre) {
        this.nombre = nombre;
    }

    public String getNombre() {
        return nombre;
    }

    public static DiaSemana fromNombre(String nombre) {
        return Arrays.stream(values())
                .filter(dia -> dia.nombre.equalsIgnoreCase(nombre))
                .findFirst()
                .orElseThrow(() -> new IllegalArgumentException("Día de la semana no válido: " + nombre));
    }
}