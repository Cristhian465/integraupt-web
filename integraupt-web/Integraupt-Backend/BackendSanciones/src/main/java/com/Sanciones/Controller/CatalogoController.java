package com.Sanciones.controller;

import com.Sanciones.dto.catalogo.EscuelaCatalogResponse;
import com.Sanciones.dto.catalogo.FacultadCatalogResponse;
import com.Sanciones.model.Escuela;
import com.Sanciones.model.Facultad;
import com.Sanciones.service.CatalogoService;
import org.springframework.web.bind.annotation.CrossOrigin;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

import java.util.List;
import java.util.stream.Collectors;

@RestController
@RequestMapping("/api/catalogos")
@CrossOrigin(origins = "*")
public class CatalogoController {

    private final CatalogoService catalogoService;

    public CatalogoController(CatalogoService catalogoService) {
        this.catalogoService = catalogoService;
    }

    @GetMapping("/facultades")
    public List<FacultadCatalogResponse> listarFacultades() {
        return catalogoService.obtenerFacultades()
                .stream()
                .map(this::mapearFacultad)
                .collect(Collectors.toList());
    }

    @GetMapping("/escuelas")
    public List<EscuelaCatalogResponse> listarEscuelas(@RequestParam(value = "facultadId", required = false) Long facultadId) {
        return catalogoService.obtenerEscuelas(facultadId)
                .stream()
                .map(this::mapearEscuela)
                .collect(Collectors.toList());
    }

    private FacultadCatalogResponse mapearFacultad(Facultad facultad) {
        return new FacultadCatalogResponse(facultad.getId(), facultad.getNombre(), facultad.getAbreviatura());
    }

    private EscuelaCatalogResponse mapearEscuela(Escuela escuela) {
        String facultadNombre = escuela.getFacultad() != null ? escuela.getFacultad().getNombre() : null;
        return new EscuelaCatalogResponse(escuela.getId(), escuela.getNombre(), escuela.getFacultadId(), facultadNombre);
    }
}