package com.reserva.controller;

import com.reserva.dto.ReservaActualizacionRequest;
import com.reserva.dto.ReservaCreacionResponse;
import com.reserva.exception.ReservaValidationException;
import com.reserva.model.Reserva;
import com.reserva.dto.ReservaDetalleResponse;
import com.reserva.dto.ReservaUsuarioResumen;
import com.reserva.service.ReservaService;
import jakarta.validation.Valid;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.ExceptionHandler;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.PutMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;
import java.util.List;
import java.util.Optional;
import org.springframework.web.bind.annotation.RequestParam;


@RestController
@RequestMapping("/api/reservas")
public class ReservaController {

    private final ReservaService reservaService;

    public ReservaController(ReservaService reservaService) {
        this.reservaService = reservaService;
    }

    @GetMapping
    public List<Reserva> listarReservas() {
        return reservaService.listarTodas();
    }

    @GetMapping("/{id}")
    public ResponseEntity<Reserva> obtenerReserva(@PathVariable Integer id) {
        return reservaService.buscarPorId(id)
                .map(ResponseEntity::ok)
                .orElse(ResponseEntity.notFound().build());
    }
    @GetMapping("/usuario/{usuarioId}")
    public ResponseEntity<List<ReservaDetalleResponse>> obtenerReservasPorUsuario(
            @PathVariable Integer usuarioId,
            @RequestParam(name = "estado", required = false) List<String> estados) {
        List<ReservaDetalleResponse> reservas = reservaService.listarPorUsuario(usuarioId, estados);
        return ResponseEntity.ok(reservas);
    }


    @GetMapping("/usuario/{usuarioId}/resumen")
    public ResponseEntity<List<ReservaUsuarioResumen>> obtenerResumenReservasUsuario(
            @PathVariable Integer usuarioId) {
        List<ReservaUsuarioResumen> resumen = reservaService.listarResumenPorUsuario(usuarioId);
        return ResponseEntity.ok(resumen);
    }

    @PostMapping
        public ResponseEntity<ReservaCreacionResponse> crearReserva(@Valid @RequestBody Reserva reserva) {
            ReservaCreacionResponse nuevaReserva = reservaService.crearReserva(reserva);
            return ResponseEntity.status(HttpStatus.CREATED).body(nuevaReserva);
        }

    @GetMapping("/{id}/qr")
    public ResponseEntity<ReservaCreacionResponse> obtenerQrReserva(@PathVariable Integer id) {
        Optional<ReservaCreacionResponse> response = reservaService.obtenerReservaConQr(id);
        return response.map(ResponseEntity::ok).orElse(ResponseEntity.notFound().build());
    }

        @PutMapping("/{id}")
        public ResponseEntity<Reserva> actualizarReserva(
                @PathVariable Integer id,
                @Valid @RequestBody ReservaActualizacionRequest request) {
            Reserva reservaActualizada = reservaService.actualizarReserva(id, request);
            return ResponseEntity.ok(reservaActualizada);
        }

        @DeleteMapping("/{id}")
        public ResponseEntity<Void> eliminarReserva(@PathVariable Integer id) {
            reservaService.eliminarReserva(id);
            return ResponseEntity.noContent().build();
        }

        @ExceptionHandler(IllegalArgumentException.class)
        public ResponseEntity<String> manejarArgumentoInvalido(IllegalArgumentException ex) {
            return ResponseEntity.status(HttpStatus.NOT_FOUND).body(ex.getMessage());
        }

    @ExceptionHandler(ReservaValidationException.class)
    public ResponseEntity<String> manejarReservaInvalida(ReservaValidationException ex) {
        return ResponseEntity.status(HttpStatus.BAD_REQUEST).body(ex.getMessage());
    }
}
