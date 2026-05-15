package com.reserva.dto;

import com.reserva.model.Reserva;

public class ReservaCreacionResponse {

    private final Reserva reserva;
    private final ReservaQrResponse qr;

    public ReservaCreacionResponse(Reserva reserva, ReservaQrResponse qr) {
        this.reserva = reserva;
        this.qr = qr;
    }

    public Reserva getReserva() {
        return reserva;
    }

    public ReservaQrResponse getQr() {
        return qr;
    }
}