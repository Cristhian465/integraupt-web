package com.integraupt.qr.controller;

import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;
import org.springframework.web.bind.annotation.CrossOrigin;

import com.integraupt.qr.dto.ReservaQrRequest;
import com.integraupt.qr.dto.ReservaQrResponse;
import com.integraupt.qr.dto.ReservaQrVerificationResponse;
import com.integraupt.qr.service.ReservaQrService;


import jakarta.validation.Valid;

@RestController
@RequestMapping("/api/v1/qr/reservas")
@Validated
@CrossOrigin(origins = "*")
public class ReservaQrController {

    private final ReservaQrService reservaQrService;

    public ReservaQrController(ReservaQrService reservaQrService) {
        this.reservaQrService = reservaQrService;
    }

    @PostMapping
    public ResponseEntity<ReservaQrResponse> generarQr(@Valid @RequestBody ReservaQrRequest request) {
        ReservaQrResponse response = reservaQrService.crearQr(request);
        return ResponseEntity.status(HttpStatus.CREATED).body(response);
    }

    @GetMapping("/{token}")
    public ResponseEntity<ReservaQrVerificationResponse> verificarReserva(@PathVariable String token) {
        ReservaQrVerificationResponse response = reservaQrService.obtenerPorToken(token);
        return ResponseEntity.ok(response);
    }


    @GetMapping("/reserva/{reservaId}")
    public ResponseEntity<ReservaQrResponse> obtenerQrPorReserva(@PathVariable Long reservaId) {
        ReservaQrResponse response = reservaQrService.obtenerPorReservaId(reservaId);
        return ResponseEntity.ok(response);
    }
}