package com.horarios.controller;

import com.horarios.dto.ActualizarOcupacionRequest;
import com.horarios.dto.HorarioRequest;
import com.horarios.dto.HorarioResponse;
import com.horarios.dto.HorarioSemanalResponse;
import com.horarios.service.HorarioService;
import jakarta.validation.Valid;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.CrossOrigin;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.PutMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;
import org.springframework.web.bind.annotation.PatchMapping;

import java.util.HashMap;
import java.util.List;
import java.util.Map;

@RestController
@RequestMapping("/api/horarios")
@CrossOrigin(origins = "*")
public class HorarioController {

    private final HorarioService horarioService;

    public HorarioController(HorarioService horarioService) {
        this.horarioService = horarioService;
    }

    @GetMapping
    public List<HorarioResponse> listarHorarios() {
        return horarioService.listarTodos();
    }

    @GetMapping("/{id}")
    public ResponseEntity<Map<String, Object>> obtenerHorario(@PathVariable Integer id) {
        return horarioService.buscarPorId(id)
                .map(horario -> ResponseEntity.ok(respuestaExitosa("Horario encontrado", "horario", horario)))
                .orElse(ResponseEntity.status(HttpStatus.NOT_FOUND)
                        .body(respuestaError("Horario no encontrado")));
    }

    @GetMapping("/espacio/{espacioId}")
    public List<HorarioResponse> listarPorEspacio(@PathVariable Integer espacioId) {
        return horarioService.listarPorEspacio(espacioId);
    }

    @GetMapping("/espacio/{espacioId}/semanal")
    public List<HorarioSemanalResponse> obtenerHorarioSemanal(@PathVariable Integer espacioId) {
        return horarioService.obtenerHorarioSemanalPorEspacio(espacioId);
    }

    @GetMapping("/dia/{diaSemana}")
    public List<HorarioResponse> listarPorDia(@PathVariable String diaSemana) {
        return horarioService.listarPorDia(diaSemana);
    }

    @GetMapping("/disponibles")
    public List<HorarioResponse> listarDisponibles() {
        return horarioService.listarPorOcupacion(false);
    }

    @GetMapping("/ocupados")
    public List<HorarioResponse> listarOcupados() {
        return horarioService.listarPorOcupacion(true);
    }

    @PostMapping
    public ResponseEntity<Map<String, Object>> crearHorario(@Valid @RequestBody HorarioRequest request) {
        HorarioResponse horario = horarioService.crearHorario(request);
        return ResponseEntity.status(HttpStatus.CREATED)
                .body(respuestaExitosa("Horario creado correctamente", "horario", horario));
    }

    @PutMapping("/{id}")
    public ResponseEntity<Map<String, Object>> actualizarHorario(@PathVariable Integer id,
                                                                 @Valid @RequestBody HorarioRequest request) {
        try {
            HorarioResponse horario = horarioService.actualizarHorario(id, request);
            return ResponseEntity.ok(respuestaExitosa("Horario actualizado correctamente", "horario", horario));
        } catch (IllegalArgumentException ex) {
            return ResponseEntity.status(HttpStatus.NOT_FOUND)
                    .body(respuestaError(ex.getMessage()));
        }
    }

    @PatchMapping("/{id}/ocupacion")
    public ResponseEntity<Map<String, Object>> actualizarOcupacion(@PathVariable Integer id,
                                                                   @Valid @RequestBody ActualizarOcupacionRequest request) {
        try {
            HorarioResponse horario = horarioService.actualizarOcupacion(id, request);
            return ResponseEntity.ok(respuestaExitosa("Estado de ocupación actualizado", "horario", horario));
        } catch (IllegalArgumentException ex) {
            return ResponseEntity.status(HttpStatus.NOT_FOUND)
                    .body(respuestaError(ex.getMessage()));
        }
    }

    @DeleteMapping("/{id}")
    public ResponseEntity<Map<String, Object>> eliminarHorario(@PathVariable Integer id) {
        try {
            horarioService.eliminarHorario(id);
            return ResponseEntity.ok(respuestaExitosa("Horario eliminado correctamente", null, null));
        } catch (IllegalArgumentException ex) {
            return ResponseEntity.status(HttpStatus.NOT_FOUND)
                    .body(respuestaError(ex.getMessage()));
        }
    }

    private Map<String, Object> respuestaExitosa(String mensaje, String claveDato, Object dato) {
        Map<String, Object> respuesta = new HashMap<>();
        respuesta.put("exito", true);
        respuesta.put("mensaje", mensaje);
        if (claveDato != null && dato != null) {
            respuesta.put(claveDato, dato);
        }
        return respuesta;
    }

    private Map<String, Object> respuestaError(String mensaje) {
        Map<String, Object> respuesta = new HashMap<>();
        respuesta.put("exito", false);
        respuesta.put("mensaje", mensaje);
        return respuesta;
    }
}