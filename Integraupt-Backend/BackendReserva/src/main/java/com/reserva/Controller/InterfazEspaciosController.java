package com.reserva.controller;

import org.springframework.web.bind.annotation.RestController;
import org.springframework.web.bind.annotation.GetMapping;

import java.util.Map;

@RestController
public class InterfazEspaciosController {

    @GetMapping({"/", "/espacios"})
    public Map<String, String> mostrarInterfaz() {
        return Map.of("mensaje", "Servicio disponible");
}
}