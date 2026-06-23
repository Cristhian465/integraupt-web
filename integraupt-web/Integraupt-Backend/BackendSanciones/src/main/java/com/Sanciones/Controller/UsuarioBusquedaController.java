package com.Sanciones.controller;

import com.Sanciones.dto.usuario.UsuarioBusquedaResponse;
import com.Sanciones.service.UsuarioBusquedaService;
import org.springframework.util.StringUtils;
import org.springframework.web.bind.annotation.CrossOrigin;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

import java.util.List;

@RestController
@RequestMapping("/api/usuarios")
@CrossOrigin(origins = "*")
public class UsuarioBusquedaController {

    private final UsuarioBusquedaService usuarioBusquedaService;

    public UsuarioBusquedaController(UsuarioBusquedaService usuarioBusquedaService) {
        this.usuarioBusquedaService = usuarioBusquedaService;
    }

    @GetMapping("/busqueda")
    public List<UsuarioBusquedaResponse> buscarUsuarios(@RequestParam("tipoUsuario") String tipoUsuario,
                                                        @RequestParam(value = "rol", required = false) String rol,
                                                        @RequestParam(value = "query", required = false) String query,
                                                        @RequestParam(value = "facultadId", required = false) Long facultadId,
                                                        @RequestParam(value = "escuelaId", required = false) Long escuelaId,
                                                        @RequestParam(value = "escuelaContextoId", required = false) Long escuelaContextoId,
                                                        @RequestParam(value = "limit", required = false) Integer limit) {
        String termino = StringUtils.hasText(query) ? query.trim() : null;
        return usuarioBusquedaService.buscarUsuarios(tipoUsuario, rol, termino, facultadId, escuelaId, escuelaContextoId, limit);
    }
}