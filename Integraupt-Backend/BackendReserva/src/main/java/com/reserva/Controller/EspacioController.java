package com.reserva.controller;

import com.reserva.dto.BloqueHorarioResponse;
import com.reserva.dto.CursoComboResponse;
import com.reserva.model.Espacio;
import com.reserva.service.EspacioService;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

import java.util.List;
import java.util.stream.Collectors;

@RestController
@RequestMapping("/api/espacios")
public class EspacioController {

    private final EspacioService espacioService;

    public EspacioController(EspacioService espacioService) {
        this.espacioService = espacioService;
    }

    @GetMapping
    public List<Espacio> listarEspacios(
            @RequestParam(name = "incluirInactivos", defaultValue = "false") boolean incluirInactivos,
            @RequestParam(name = "escuelaId", required = false) Integer escuelaId) {
        if (incluirInactivos) {
            return espacioService.listarTodosPorEscuela(escuelaId);
        }
        return espacioService.listarActivosPorEscuela(escuelaId);
    }

    @GetMapping("/{espacioId}/cursos")
    public List<CursoComboResponse> listarCursosPorEspacio(@PathVariable Integer espacioId) {
        return espacioService.listarCursosActivosPorEspacio(espacioId)
                .stream()
                .map(CursoComboResponse::fromEntity)
                .collect(Collectors.toList());
    }

    @GetMapping("/{espacioId}/bloques")
    public List<BloqueHorarioResponse> listarBloquesPorEspacio(@PathVariable Integer espacioId) {
        return espacioService.listarBloquesPorEspacio(espacioId)
                .stream()
                .map(BloqueHorarioResponse::fromEntity)
                .collect(Collectors.toList());
    }
}