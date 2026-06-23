package com.horariocurso.modelo;

public enum DiaSemana {
    Lunes,
    Martes,
    Miercoles,
    Jueves,
    Viernes,
    Sabado;

    public static DiaSemana fromString(String value) {
        for (DiaSemana dia : DiaSemana.values()) {
            if (dia.name().equalsIgnoreCase(value)) {
                return dia;
            }
        }
        throw new IllegalArgumentException("Dia de la semana no soportado: " + value);
    }
}
