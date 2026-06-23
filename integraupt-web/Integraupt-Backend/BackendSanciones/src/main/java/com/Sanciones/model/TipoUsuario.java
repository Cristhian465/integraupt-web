package com.Sanciones.model;

import org.springframework.util.StringUtils;

public enum TipoUsuario {
    ESTUDIANTE,
    DOCENTE;

    public static TipoUsuario fromNombre(String valor) {
        if (!StringUtils.hasText(valor)) {
            throw new IllegalArgumentException("El tipo de usuario no puede ser vacío");
        }
        try {
            return TipoUsuario.valueOf(valor.trim().toUpperCase());
        } catch (IllegalArgumentException ex) {
            throw new IllegalArgumentException("Tipo de usuario desconocido: " + valor);
        }
    }
}