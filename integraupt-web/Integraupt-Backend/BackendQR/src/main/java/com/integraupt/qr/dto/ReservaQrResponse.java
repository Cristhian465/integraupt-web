package com.integraupt.qr.dto;

import java.time.Instant;

public class ReservaQrResponse {

    private final String token;
    private final String verificationUrl;
    private final String qrBase64;
    private final ReservaInfo reserva;
    private final Instant generadoEn;

    public ReservaQrResponse(String token, String verificationUrl, String qrBase64, ReservaInfo reserva, Instant generadoEn) {
        this.token = token;
        this.verificationUrl = verificationUrl;
        this.qrBase64 = qrBase64;
        this.reserva = reserva;
        this.generadoEn = generadoEn;
    }

    public String getToken() {
        return token;
    }

    public String getVerificationUrl() {
        return verificationUrl;
    }

    public String getQrBase64() {
        return qrBase64;
    }

    public ReservaInfo getReserva() {
        return reserva;
    }

    public Instant getGeneradoEn() {
        return generadoEn;
    }
}