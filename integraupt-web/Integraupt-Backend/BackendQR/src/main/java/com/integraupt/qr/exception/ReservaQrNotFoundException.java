package com.integraupt.qr.exception;

public class ReservaQrNotFoundException extends RuntimeException {

    public ReservaQrNotFoundException(String token) {
        super("No se encontró un QR asociado al token: " + token);
    }
}