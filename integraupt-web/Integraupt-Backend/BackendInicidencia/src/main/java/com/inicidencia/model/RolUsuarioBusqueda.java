package com.inicidencia.model;

import org.springframework.util.StringUtils;

public enum RolUsuarioBusqueda {
    ADMINISTRATIVO,
    SUPERVISOR;

    public static RolUsuarioBusqueda fromNombre(String valor) {
        if (!StringUtils.hasText(valor)) {
            return ADMINISTRATIVO;
        }

        String normalizado = valor.trim().toUpperCase();

        if ("ADMINISTRADOR".equals(normalizado)) {
            return ADMINISTRATIVO;
        }


        try {
            return RolUsuarioBusqueda.valueOf(normalizado);
        } catch (IllegalArgumentException ex) {
            throw new IllegalArgumentException("Rol de búsqueda desconocido: " + valor);
        }
    }
}