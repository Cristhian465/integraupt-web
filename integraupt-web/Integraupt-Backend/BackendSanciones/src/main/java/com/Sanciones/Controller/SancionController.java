package com.Sanciones.controller;

import com.Sanciones.dto.SancionRequest;
import com.Sanciones.dto.SancionResponse;
import com.Sanciones.dto.VerificacionSancionResponse;
import com.Sanciones.service.SancionService;
import jakarta.validation.Valid;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.CrossOrigin;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PatchMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

import java.util.List;

@RestController
@RequestMapping("/api/sanciones")
@CrossOrigin(origins = "*")
public class SancionController {

    private final SancionService sancionService;

    public SancionController(SancionService sancionService) {
        this.sancionService = sancionService;
    }

    @PostMapping
    public ResponseEntity<SancionResponse> registrar(@Valid @RequestBody SancionRequest request) {
        SancionResponse response = sancionService.registrarSancion(request);
        return ResponseEntity.status(HttpStatus.CREATED).body(response);
    }

    @GetMapping
    public ResponseEntity<List<SancionResponse>> obtenerTodas(@RequestParam(value = "rol", required = false) String rol,
                                                              @RequestParam(value = "facultadId", required = false) Long facultadId,
                                                              @RequestParam(value = "escuelaId", required = false) Long escuelaId,
                                                              @RequestParam(value = "escuelaContextoId", required = false) Long escuelaContextoId) {
        return ResponseEntity.ok(sancionService.obtenerTodas(rol, facultadId, escuelaId, escuelaContextoId));
    }

    @GetMapping("/activas")
    public ResponseEntity<List<SancionResponse>> obtenerActivas(@RequestParam(value = "rol", required = false) String rol,
                                                                @RequestParam(value = "facultadId", required = false) Long facultadId,
                                                                @RequestParam(value = "escuelaId", required = false) Long escuelaId,
                                                                @RequestParam(value = "escuelaContextoId", required = false) Long escuelaContextoId) {
        return ResponseEntity.ok(sancionService.obtenerActivas(rol, facultadId, escuelaId, escuelaContextoId));
    }

    @PatchMapping("/{id}/levantar")
    public ResponseEntity<SancionResponse> levantar(@PathVariable("id") Long id) {
        return ResponseEntity.ok(sancionService.levantarSancion(id));
    }

    @GetMapping("/verificacion")
    public ResponseEntity<VerificacionSancionResponse> verificar(@RequestParam("usuarioId") Long usuarioId,
                                                                 @RequestParam("tipoUsuario") String tipoUsuario) {
        return ResponseEntity.ok(sancionService.verificarUsuarioSancionado(usuarioId, tipoUsuario));
    }

    @GetMapping("/estado")
    public ResponseEntity<Boolean> tieneSancionActiva(@RequestParam("usuarioId") Long usuarioId,
                                                      @RequestParam("tipoUsuario") String tipoUsuario) {
        return ResponseEntity.ok(sancionService.tieneSancionActiva(usuarioId, tipoUsuario));
    }
}