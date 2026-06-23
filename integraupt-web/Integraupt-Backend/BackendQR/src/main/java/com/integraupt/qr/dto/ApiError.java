package com.integraupt.qr.dto;

import java.time.Instant;
import java.util.List;

public class ApiError {

    private final Instant timestamp;
    private final int status;
    private final String error;
    private final List<String> mensajes;

    public ApiError(Instant timestamp, int status, String error, List<String> mensajes) {
        this.timestamp = timestamp;
        this.status = status;
        this.error = error;
        this.mensajes = mensajes;
    }

    public Instant getTimestamp() {
        return timestamp;
    }

    public int getStatus() {
        return status;
    }

    public String getError() {
        return error;
    }

    public List<String> getMensajes() {
        return mensajes;
    }
}