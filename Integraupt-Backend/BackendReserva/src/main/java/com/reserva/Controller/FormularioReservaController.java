package com.reserva.controller;

import com.reserva.dto.FormularioReservaResponse;
import com.reserva.model.Espacio;
import com.reserva.service.EspacioService;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

import java.util.List;

@RestController
public class FormularioReservaController {

    private final EspacioService espacioService;

    public FormularioReservaController(EspacioService espacioService) {
        this.espacioService = espacioService;
    }

    @GetMapping("/reservas/formulario")
    public FormularioReservaResponse mostrarFormulario(
            @RequestParam(name = "espacioId", required = false) Integer espacioId,
            @RequestParam(name = "escuelaId", required = false) Integer escuelaId) {
        List<Espacio> espaciosDisponibles = espacioService.listarActivosPorEscuela(escuelaId);

        Espacio espacioSeleccionado = null;
        if (espacioId != null) {
            espacioSeleccionado = espacioService.buscarPorId(espacioId).orElse(null);
            if (espacioSeleccionado != null && espaciosDisponibles.stream().noneMatch(e -> e.getId().equals(espacioId))) {
                espaciosDisponibles.add(0, espacioSeleccionado);
            }

        }

        return new FormularioReservaResponse(
                espaciosDisponibles,
                espacioId,
                espacioSeleccionado != null
                        ? espacioSeleccionado.getCodigo() + " - " + espacioSeleccionado.getNombre()
                        : null
        );
    }
}