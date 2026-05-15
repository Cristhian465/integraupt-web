package com.inicidencia.controller;

import com.inicidencia.dto.DisponibilidadIncidenciaResponse;
import com.inicidencia.dto.IncidenciaGestionResponse;
import com.inicidencia.dto.IncidenciaRequest;
import com.inicidencia.dto.IncidenciaResponse;
import com.inicidencia.service.IncidenciaService;
import jakarta.validation.Valid;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.ExceptionHandler;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;
import com.inicidencia.dto.ReservaIncidenciaResumen;
import org.springframework.web.bind.annotation.CrossOrigin;

import java.util.List;

@RestController
@RequestMapping("/api/incidencias")
@CrossOrigin(origins = "*")
public class IncidenciaController {

    private final IncidenciaService incidenciaService;

    public IncidenciaController(IncidenciaService incidenciaService) {
        this.incidenciaService = incidenciaService;
    }

    @PostMapping
    public ResponseEntity<IncidenciaResponse> registrarIncidencia(@Valid @RequestBody IncidenciaRequest request) {
        IncidenciaResponse incidencia = incidenciaService.registrarIncidencia(request);
        return ResponseEntity.status(HttpStatus.CREATED).body(incidencia);
    }

    @GetMapping
    public List<IncidenciaGestionResponse> listarParaGestion(
            @RequestParam(value = "rol", required = false) String rol,
            @RequestParam(value = "facultadId", required = false) Long facultadId,
            @RequestParam(value = "escuelaId", required = false) Long escuelaId,
            @RequestParam(value = "escuelaContextoId", required = false) Long escuelaContextoId,
            @RequestParam(value = "espacioId", required = false) Integer espacioId,
            @RequestParam(value = "search", required = false) String search
    ) {
        return incidenciaService.listarIncidenciasParaGestion(
                rol,
                facultadId,
                escuelaId,
                escuelaContextoId,
                espacioId,
                search
        );
    }

    @GetMapping("/reserva/{reservaId}")
    public List<IncidenciaResponse> listarPorReserva(@PathVariable Integer reservaId) {
        return incidenciaService.listarPorReserva(reservaId);
    }

    @GetMapping("/reserva/{reservaId}/disponibilidad")
    public DisponibilidadIncidenciaResponse verificarDisponibilidad(@PathVariable Integer reservaId) {
        return incidenciaService.verificarDisponibilidad(reservaId);
    }
    @GetMapping("/usuario/{usuarioId}/reservas")
    public List<ReservaIncidenciaResumen> listarReservasPorUsuario(@PathVariable Integer usuarioId) {
        return incidenciaService.listarReservasParaUsuario(usuarioId);
    }

    @ExceptionHandler(IllegalArgumentException.class)
    public ResponseEntity<String> manejarNoEncontrado(IllegalArgumentException ex) {
        return ResponseEntity.status(HttpStatus.NOT_FOUND).body(ex.getMessage());
    }

    @ExceptionHandler(IllegalStateException.class)
    public ResponseEntity<String> manejarEstadoInvalido(IllegalStateException ex) {
        return ResponseEntity.status(HttpStatus.CONFLICT).body(ex.getMessage());
    }
}