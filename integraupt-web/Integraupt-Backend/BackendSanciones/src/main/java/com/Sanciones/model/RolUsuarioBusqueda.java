package com.Sanciones.model;

import org.springframework.util.StringUtils;

public enum RolUsuarioBusqueda {
    ADMINISTRATIVO,
    SUPERVISOR;

    public static RolUsuarioBusqueda fromNombre(String valor) {
        if (!StringUtils.hasText(valor)) {
            return ADMINISTRATIVO;
        }

        try {
            return RolUsuarioBusqueda.valueOf(valor.trim().toUpperCase());
        } catch (IllegalArgumentException ex) {
            throw new IllegalArgumentException("Rol de búsqueda desconocido: " + valor);
        }
    }
}