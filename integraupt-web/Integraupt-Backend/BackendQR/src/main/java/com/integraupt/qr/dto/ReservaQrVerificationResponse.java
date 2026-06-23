package com.integraupt.qr.dto;

import java.time.Instant;

public class ReservaQrVerificationResponse {

    private final String token;
    private final ReservaInfo reserva;
    private final Instant generadoEn;
    private final Instant verificadoEn;

    public ReservaQrVerificationResponse(String token, ReservaInfo reserva, Instant generadoEn, Instant verificadoEn) {
        this.token = token;
        this.reserva = reserva;
        this.generadoEn = generadoEn;
        this.verificadoEn = verificadoEn;
    }

    public String getToken() {
        return token;
    }

    public ReservaInfo getReserva() {
        return reserva;
    }

    public Instant getGeneradoEn() {
        return generadoEn;
    }

    public Instant getVerificadoEn() {
        return verificadoEn;
    }
}