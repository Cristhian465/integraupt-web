package com.reserva.controller;

import com.reserva.dto.EscuelaResponse;
import com.reserva.dto.FacultadResponse;
import com.reserva.service.CatalogoService;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

import java.util.List;
import java.util.stream.Collectors;

@RestController
@RequestMapping("/api/catalogos")
public class CatalogoController {

    private final CatalogoService catalogoService;

    public CatalogoController(CatalogoService catalogoService) {
        this.catalogoService = catalogoService;
    }

    @GetMapping("/facultades")
    public List<FacultadResponse> listarFacultades() {
        return catalogoService.listarFacultades()
                .stream()
                .map(FacultadResponse::fromEntity)
                .collect(Collectors.toList());
    }

    @GetMapping("/escuelas")
    public List<EscuelaResponse> listarEscuelas(
            @RequestParam(name = "facultadId", required = false) Integer facultadId) {
        return catalogoService.listarEscuelas(facultadId)
                .stream()
                .map(EscuelaResponse::fromEntity)
                .collect(Collectors.toList());
    }
}