package com.reserva.dto;

import java.time.Instant;

public class ReservaQrResponse {

    private String token;
    private String verificationUrl;
    private String qrBase64;
    private ReservaQrInfo reserva;
    private Instant generadoEn;

    public String getToken() {
        return token;
    }

    public void setToken(String token) {
        this.token = token;
    }

    public String getVerificationUrl() {
        return verificationUrl;
    }

    public void setVerificationUrl(String verificationUrl) {
        this.verificationUrl = verificationUrl;
    }

    public String getQrBase64() {
        return qrBase64;
    }

    public void setQrBase64(String qrBase64) {
        this.qrBase64 = qrBase64;
    }

    public ReservaQrInfo getReserva() {
        return reserva;
    }

    public void setReserva(ReservaQrInfo reserva) {
        this.reserva = reserva;
    }

    public Instant getGeneradoEn() {
        return generadoEn;
    }

    public void setGeneradoEn(Instant generadoEn) {
        this.generadoEn = generadoEn;
    }
}